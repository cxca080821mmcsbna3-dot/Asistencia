/**
 * CECYTEM — WhatsApp Local Bridge
 * ─────────────────────────────────────────────────────────────────
 * Servidor Node.js que conecta whatsapp-web.js con el sistema PHP.
 * Corre en http://localhost:3000 (solo accesible desde el servidor).
 *
 * ENDPOINTS:
 *   GET  /status        → estado de la conexión (conectado / qr / desconectado)
 *   GET  /qr            → imagen QR en base64 para escanear
 *   POST /send          → enviar mensaje { to: "521234...", message: "Hola" }
 *   POST /logout        → cerrar sesión de WhatsApp
 *
 * INICIO:
 *   cd wa-server && npm install && node server.js
 * ─────────────────────────────────────────────────────────────────
 */

const { Client, LocalAuth } = require('whatsapp-web.js');
const express  = require('express');
const cors     = require('cors');
const qrcode   = require('qrcode');

const app  = express();
const PORT = 3000;

app.use(express.json());
app.use(cors({ origin: 'http://localhost' })); // Solo acepta peticiones desde localhost

// ── Estado global ────────────────────────────────────────────────
let estado       = 'iniciando';  // iniciando | esperando_qr | conectado | desconectado
let qrActual     = null;         // QR en base64 para mostrar en navegador
let clienteListo = false;

// ── Inicializar cliente WhatsApp ─────────────────────────────────
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: './session' }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
        ],
    },
});

client.on('qr', async (qr) => {
    estado = 'esperando_qr';
    clienteListo = false;
    // Convertir QR a imagen base64
    qrActual = await qrcode.toDataURL(qr);
    console.log('[WA] QR generado — abre el panel para escanearlo');
});

client.on('ready', () => {
    estado       = 'conectado';
    clienteListo = true;
    qrActual     = null;
    console.log('[WA] ✅ WhatsApp conectado —', client.info?.pushname || 'listo');
});

client.on('authenticated', () => {
    console.log('[WA] Sesión autenticada');
});

client.on('auth_failure', (msg) => {
    estado = 'desconectado';
    clienteListo = false;
    console.error('[WA] ❌ Error de autenticación:', msg);
});

client.on('disconnected', (reason) => {
    estado = 'desconectado';
    clienteListo = false;
    qrActual = null;
    console.log('[WA] Desconectado:', reason);
    // Reintentar conexión después de 10 segundos
    setTimeout(() => {
        console.log('[WA] Reintentando conexión…');
        client.initialize();
    }, 10000);
});

client.initialize();

// ── ENDPOINTS ────────────────────────────────────────────────────

// GET /status — Estado actual
app.get('/status', (req, res) => {
    const info = clienteListo ? {
        nombre:   client.info?.pushname || '—',
        telefono: client.info?.wid?.user || '—',
    } : null;

    res.json({
        ok:     clienteListo,
        estado,
        info,
    });
});

// GET /qr — QR en base64
app.get('/qr', (req, res) => {
    if (estado === 'conectado') {
        return res.json({ ok: true, conectado: true, msg: 'Ya está conectado, no necesitas QR' });
    }
    if (!qrActual) {
        return res.json({ ok: false, conectado: false, msg: 'QR aún no generado, espera unos segundos…' });
    }
    res.json({ ok: true, conectado: false, qr: qrActual });
});

// POST /send — Enviar mensaje
// Body: { to: "5211234567890", message: "Texto del mensaje" }
app.post('/send', async (req, res) => {
    if (!clienteListo) {
        return res.status(503).json({ ok: false, msg: 'WhatsApp no está conectado' });
    }

    const { to, message } = req.body;
    if (!to || !message) {
        return res.status(400).json({ ok: false, msg: 'Faltan campos: to, message' });
    }

    // Normalizar número: quitar +, espacios, guiones; agregar @c.us
    const numero = to.replace(/[^0-9]/g, '');
    if (numero.length < 10) {
        return res.status(400).json({ ok: false, msg: 'Número de teléfono inválido: ' + to });
    }
    const chatId = numero + '@c.us';

    try {
        await client.sendMessage(chatId, message);
        console.log('[WA] ✉️ Enviado a', numero);
        res.json({ ok: true, msg: 'Mensaje enviado a ' + numero });
    } catch (err) {
        console.error('[WA] Error al enviar:', err.message);
        res.status(500).json({ ok: false, msg: 'Error al enviar: ' + err.message });
    }
});

// POST /logout — Cerrar sesión
app.post('/logout', async (req, res) => {
    try {
        await client.logout();
        estado = 'desconectado';
        clienteListo = false;
        res.json({ ok: true, msg: 'Sesión cerrada' });
    } catch (err) {
        res.status(500).json({ ok: false, msg: err.message });
    }
});

// ── Iniciar servidor ─────────────────────────────────────────────
app.listen(PORT, '127.0.0.1', () => {
    console.log(`[WA] Servidor corriendo en http://127.0.0.1:${PORT}`);
    console.log('[WA] Esperando que WhatsApp inicialice…');
});
