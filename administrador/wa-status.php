<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/Conexion.php";
if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WhatsApp Local — CECYTEM</title>
<link rel="stylesheet" href="css/menu.css?v=2.1">
<style>
body { font-family: 'Segoe UI', sans-serif; }
.wa-main {
    max-width: 620px; margin: 2rem auto; padding: 0 1rem;
}
.wa-card {
    background: #fff; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.1);
    overflow: hidden;
}
.wa-header {
    background: linear-gradient(135deg, #25d366, #128c7e);
    color: #fff; padding: 1.5rem 2rem;
    display: flex; align-items: center; gap: 1rem;
}
.wa-header h1 { margin:0; font-size: 1.4rem; }
.wa-header p  { margin:.3rem 0 0; font-size:.85rem; opacity:.85; }
.wa-body { padding: 2rem; }

.estado-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .4rem 1rem; border-radius: 20px; font-weight: 700;
    font-size: .9rem; margin-bottom: 1.5rem;
}
.estado-conectado   { background:#e8f5e9; color:#2e7d32; border:1.5px solid #a5d6a7; }
.estado-qr          { background:#fff8e1; color:#f57f17; border:1.5px solid #ffe082; }
.estado-desconectado{ background:#fce4ec; color:#c62828; border:1.5px solid #ef9a9a; }
.estado-iniciando   { background:#e3f2fd; color:#1565c0; border:1.5px solid #90caf9; }

.qr-box {
    text-align: center; padding: 1.5rem;
    background: #f9f9f9; border-radius: 10px;
    border: 2px dashed #ccc; margin-bottom: 1.5rem;
}
.qr-box img { max-width: 240px; border-radius: 8px; }
.qr-steps {
    font-size: .85rem; color: #555; text-align: left;
    background: #f0f7ff; border-radius: 8px; padding: 1rem 1.2rem;
    border-left: 3px solid #1976d2; margin-bottom: 1.5rem;
}
.qr-steps ol { margin: .5rem 0 0 1rem; padding: 0; }
.qr-steps li { margin-bottom: .4rem; }

.wa-info-box {
    background: #e8f5e9; border-radius: 10px;
    padding: 1.2rem 1.5rem; margin-bottom: 1.5rem;
    border: 1.5px solid #a5d6a7;
}
.wa-info-box .nombre   { font-size: 1.2rem; font-weight: 700; color: #2e7d32; }
.wa-info-box .telefono { font-size: .9rem; color: #555; margin-top: .2rem; }

.btn-wa {
    padding: .7rem 1.4rem; border: none; border-radius: 8px;
    font-size: .9rem; font-weight: 600; cursor: pointer;
    font-family: inherit; transition: all .15s;
}
.btn-refrescar { background: #1976d2; color: #fff; }
.btn-refrescar:hover { background: #1565c0; }
.btn-cerrar    { background: #fff; color: #c62828; border: 1.5px solid #ef9a9a; margin-left: .6rem; }
.btn-cerrar:hover { background: #fce4ec; }

.servidor-off {
    text-align: center; padding: 2rem;
    background: #fff3e0; border-radius: 10px;
    border: 1.5px solid #ffb74d; margin-bottom: 1.5rem;
}
.servidor-off code {
    display: block; background: #263238; color: #a5d6a7;
    padding: .8rem 1.2rem; border-radius: 6px;
    font-size: .88rem; text-align: left; margin-top: .8rem;
    user-select: all;
}

.volver-link {
    display: inline-flex; align-items: center; gap: .4rem;
    color: #555; font-size: .85rem; text-decoration: none;
    margin-bottom: 1.5rem;
}
.volver-link:hover { color: #128c7e; }
</style>
</head>
<body>
<div class="contenedor">
  <div class="contenedor1" style="min-height:140px;height:auto;">
    <video autoplay muted loop style="height:140px;"><source src="img/video.mp4" type="video/mp4"></video>
    <?php include_once "layout/header_admin.php"; ?>
  </div>
</div>

<main>
<div class="wa-main">

  <a href="registroAsistencia.php?tab=whatsapp" class="volver-link">← Volver a Asistencia</a>

  <div class="wa-card">
    <div class="wa-header">
      <div style="font-size:2.5rem;">📱</div>
      <div>
        <h1>WhatsApp Local</h1>
        <p>Conexión gratuita mediante whatsapp-web.js</p>
      </div>
    </div>

    <div class="wa-body" id="wa-contenido">
      <div style="text-align:center;padding:2rem;color:#888;">
        <div id="spinner" style="font-size:2rem;">⏳</div>
        <p>Verificando estado del servidor…</p>
      </div>
    </div>
  </div>

</div>
</main>

<script>
let intervaloQR = null;

async function verificarEstado() {
    try {
        const res = await fetch('ajax/wa-proxy.php?action=status');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        renderEstado(data);
    } catch (e) {
        renderServidorOff();
    }
}

function renderServidorOff() {
    detenerIntervalo();
    document.getElementById('wa-contenido').innerHTML = `
        <div class="servidor-off">
            <div style="font-size:2rem;margin-bottom:.8rem;">🔌</div>
            <strong>El servidor Node.js no está corriendo</strong>
            <p style="font-size:.88rem;color:#555;margin:.5rem 0;">
                Abre una terminal en el servidor y ejecuta:
            </p>
            <code>cd ${escHtml('<?= addslashes(realpath(__DIR__)) ?>')}/wa-server<br>npm install<br>node server.js</code>
            <p style="font-size:.8rem;color:#888;margin-top:.8rem;">
                Si es la primera vez, <code>npm install</code> puede tardar 1-2 minutos.
            </p>
        </div>
        <button class="btn-wa btn-refrescar" onclick="verificarEstado()">🔄 Volver a verificar</button>
    `;
}

function renderEstado(data) {
    const body = document.getElementById('wa-contenido');

    if (data.ok && data.estado === 'conectado') {
        detenerIntervalo();
        body.innerHTML = `
            <span class="estado-badge estado-conectado">✅ Conectado</span>
            <div class="wa-info-box">
                <div class="nombre">📱 ${escHtml(data.info?.nombre || '—')}</div>
                <div class="telefono">+${escHtml(data.info?.telefono || '—')}</div>
                <div style="font-size:.8rem;color:#666;margin-top:.5rem;">
                    Sesión activa — los mensajes saldrán desde este número
                </div>
            </div>
            <p style="font-size:.88rem;color:#555;margin-bottom:1.5rem;">
                ✅ Todo listo. Configura el proveedor como <strong>"WhatsApp Local (Gratis)"</strong>
                en la pestaña 📱 WhatsApp del sistema y comienza a enviar notificaciones.
            </p>
            <button class="btn-wa btn-refrescar" onclick="verificarEstado()">🔄 Refrescar</button>
            <button class="btn-wa btn-cerrar" onclick="cerrarSesion()">🔌 Cerrar sesión</button>
        `;
    } else if (data.estado === 'esperando_qr') {
        cargarQR(body);
        if (!intervaloQR) {
            intervaloQR = setInterval(() => verificarEstado(), 3000);
        }
    } else {
        body.innerHTML = `
            <span class="estado-badge estado-iniciando">⏳ Iniciando…</span>
            <p style="font-size:.88rem;color:#555;">
                El servidor está arrancando. Espera unos segundos…
            </p>
            <button class="btn-wa btn-refrescar" onclick="verificarEstado()">🔄 Refrescar</button>
        `;
        setTimeout(verificarEstado, 3000);
    }
}

async function cargarQR(body) {
    try {
        const res = await fetch('ajax/wa-proxy.php?action=qr');
        const data = await res.json();
        if (data.conectado) { verificarEstado(); return; }

        body.innerHTML = `
            <span class="estado-badge estado-qr">📷 Esperando escaneo</span>
            <div class="qr-steps">
                <strong>Cómo conectar:</strong>
                <ol>
                    <li>Abre WhatsApp en el celular de la escuela</li>
                    <li>Ve a <strong>Menú → Dispositivos vinculados</strong></li>
                    <li>Toca <strong>"Vincular un dispositivo"</strong></li>
                    <li>Escanea el código QR de abajo</li>
                </ol>
            </div>
            ${data.qr
                ? `<div class="qr-box">
                       <img src="${data.qr}" alt="QR WhatsApp">
                       <p style="font-size:.8rem;color:#888;margin:.7rem 0 0;">
                           El QR se actualiza automáticamente
                       </p>
                   </div>`
                : `<div class="qr-box" style="padding:2rem;">
                       <div style="font-size:1.5rem;">⏳</div>
                       <p style="color:#888;margin:.5rem 0 0;">Generando QR…</p>
                   </div>`
            }
            <button class="btn-wa btn-refrescar" onclick="verificarEstado()">🔄 Refrescar QR</button>
        `;
    } catch(e) {
        renderServidorOff();
    }
}

async function cerrarSesion() {
    if (!confirm('¿Cerrar la sesión de WhatsApp?\nTendrás que volver a escanear el QR para reconectar.')) return;
    await fetch('ajax/wa-proxy.php?action=logout', {method:'POST'});
    verificarEstado();
}

function detenerIntervalo() {
    if (intervaloQR) { clearInterval(intervaloQR); intervaloQR = null; }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Iniciar
verificarEstado();
</script>
</body>
</html>
