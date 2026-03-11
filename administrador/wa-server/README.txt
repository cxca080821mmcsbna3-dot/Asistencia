# CECYTEM — WhatsApp Local (wa-server)

Servidor Node.js que conecta whatsapp-web.js con el sistema PHP.
Completamente GRATIS. No requiere APIs de pago.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## REQUISITOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- Node.js 18 o superior  →  https://nodejs.org
  (verifica con: node --version)

- El servidor donde corre PHP debe tener acceso a internet
  (para que WhatsApp Web funcione)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## INSTALACIÓN (una sola vez)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. Abrir terminal y entrar a esta carpeta:
   cd ruta/del/proyecto/Asistencia/administrador/wa-server

2. Instalar dependencias:
   npm install

3. Iniciar el servidor:
   node server.js

4. Abrir en el navegador:
   http://localhost/Asistencia/administrador/wa-status.php

5. Escanear el QR con el WhatsApp de la escuela
   (igual que WhatsApp Web)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## MANTENER ACTIVO (opcional pero recomendado)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Instalar PM2 para que el servidor reinicie automáticamente:

   npm install -g pm2
   pm2 start server.js --name cecytem-wa
   pm2 save
   pm2 startup

Con esto el servidor arranca solo cuando reinicia la computadora.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## NOTAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- La sesión se guarda en la carpeta /session — no la borres
- Si se desconecta, el servidor reintenta sola en 10 segundos
- Solo es accesible desde localhost (127.0.0.1) por seguridad
- El número de WhatsApp debe tener el celular con internet

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## CONFIGURACIÓN EN EL SISTEMA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

En la pestaña 📱 WhatsApp del sistema:
- Proveedor: "WhatsApp Local (Gratis)"
- No requiere ninguna clave
- Solo asegúrate de que el servidor Node.js esté corriendo
