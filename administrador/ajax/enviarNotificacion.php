<?php
/**
 * enviarNotificacion.php
 * ──────────────────────────────────────────────────────────────────────────
 * Infraestructura WhatsApp — PREPARADO para conectar con cualquier proveedor.
 *
 * Proveedores soportados en config/whatsapp.json → "proveedor":
 *   - "callmebot"   → CallMeBot (gratuito, API key personal)
 *   - "twilio"      → Twilio WhatsApp sandbox / número real
 *   - "ultramsg"    → UltraMsg (WhatsApp Business API)
 *   - "360dialog"   → 360dialog (WhatsApp Business API)
 *
 * Payload esperado (POST JSON):
 * {
 *   "tipo": "individual" | "grupal",
 *   "destinatarios": ["+521234567890", ...],   // individual
 *   "id_grupo": 5,                              // grupal → busca teléfonos del grupo
 *   "categoria": "falta" | "tardanza" | "incidencia" | "personalizado",
 *   "mensaje": "Texto del mensaje",
 *   "id_alumno": 123                           // opcional, para adjuntar nombre
 * }
 * ──────────────────────────────────────────────────────────────────────────
 */
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sin autorización']); exit;
}

// ── Leer configuración WhatsApp ──
$configFile = __DIR__ . '/../config/whatsapp.json';
if (!file_exists($configFile)) {
    echo json_encode(['ok'=>false,'msg'=>'WhatsApp no configurado. Ve a ⚙️ WhatsApp para configurar.']);
    exit;
}
$waConfig = json_decode(file_get_contents($configFile), true);
if (!($waConfig['activo'] ?? false)) {
    echo json_encode(['ok'=>false,'msg'=>'Las notificaciones WhatsApp están desactivadas en la configuración.']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$tipo       = $data['tipo']       ?? 'individual';  // individual | grupal
$categoria  = $data['categoria']  ?? 'personalizado';
$mensaje    = trim($data['mensaje'] ?? '');
$id_alumno  = intval($data['id_alumno'] ?? 0);
$id_grupo_wa= intval($data['id_grupo']  ?? 0);

if (empty($mensaje)) {
    echo json_encode(['ok'=>false,'msg'=>'El mensaje no puede estar vacío']); exit;
}

// ── Resolver destinatarios ──
$destinatarios = [];

if ($tipo === 'grupal' && $id_grupo_wa) {
    // Obtener teléfonos de todos los alumnos del grupo
    $stmt = $pdo->prepare("
        SELECT a.nombre, a.apellidos, a.telefono
        FROM alumno a
        WHERE a.id_grupo = ? AND a.telefono IS NOT NULL AND a.telefono <> ''
    ");
    $stmt->execute([$id_grupo_wa]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($alumnos as $al) {
        $tel = preg_replace('/[^0-9+]/', '', $al['telefono']);
        if (strlen($tel) >= 10) $destinatarios[] = $tel;
    }
} elseif (!empty($data['destinatarios'])) {
    foreach ((array)$data['destinatarios'] as $tel) {
        $tel = preg_replace('/[^0-9+]/', '', $tel);
        if (strlen($tel) >= 10) $destinatarios[] = $tel;
    }
} elseif ($id_alumno) {
    $stmt = $pdo->prepare("SELECT telefono, nombre, apellidos FROM alumno WHERE id_alumno = ?");
    $stmt->execute([$id_alumno]);
    $al = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($al && $al['telefono']) {
        $tel = preg_replace('/[^0-9+]/', '', $al['telefono']);
        if (strlen($tel) >= 10) $destinatarios[] = $tel;
    }
}

if (empty($destinatarios)) {
    echo json_encode(['ok'=>false,'msg'=>'No se encontraron destinatarios con teléfono registrado']); exit;
}

// ── Enviar según proveedor ──
$proveedor = $waConfig['proveedor'] ?? 'callmebot';
$enviados  = 0;
$errores   = [];

foreach ($destinatarios as $telefono) {
    $resultado = enviarMensaje($proveedor, $waConfig, $telefono, $mensaje);
    if ($resultado['ok']) {
        $enviados++;
    } else {
        $errores[] = $telefono . ': ' . $resultado['msg'];
    }
}

// ── Log en BD ──
try {
    // Solo si existe la tabla (la creamos si no)
    $pdo->exec("CREATE TABLE IF NOT EXISTS notificaciones_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha_envio DATETIME DEFAULT NOW(),
        categoria VARCHAR(50),
        tipo VARCHAR(20),
        destinatarios INT,
        enviados INT,
        mensaje TEXT,
        admin_nombre VARCHAR(100),
        errores TEXT
    )");
    $pdo->prepare("INSERT INTO notificaciones_log
        (categoria, tipo, destinatarios, enviados, mensaje, admin_nombre, errores)
        VALUES (?,?,?,?,?,?,?)")
    ->execute([
        $categoria, $tipo, count($destinatarios), $enviados,
        substr($mensaje,0,500), $_SESSION['nombre'],
        empty($errores) ? null : implode('; ', $errores)
    ]);
} catch(Exception $ig) {}

echo json_encode([
    'ok'          => $enviados > 0,
    'enviados'    => $enviados,
    'total'       => count($destinatarios),
    'errores'     => $errores,
    'msg'         => $enviados > 0
        ? "✅ $enviados/" . count($destinatarios) . " mensajes enviados"
        : "❌ No se pudo enviar ningún mensaje",
]);

// ════════════════════════════════════════════════════════════════
// FUNCIÓN ENVIADOR — agregar soporte para más proveedores aquí
// ════════════════════════════════════════════════════════════════
function enviarMensaje(string $proveedor, array $cfg, string $telefono, string $mensaje): array {
    switch ($proveedor) {

        // ── CallMeBot (gratuito — requiere apikey personal por número) ──
        case 'callmebot':
            $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
                'phone'   => $telefono,
                'text'    => $mensaje,
                'apikey'  => $cfg['callmebot_apikey'] ?? '',
            ]);
            $resp = @file_get_contents($url);
            return ['ok' => $resp !== false, 'msg' => $resp ?: 'Sin respuesta'];

        // ── Twilio WhatsApp ──
        case 'twilio':
            $sid   = $cfg['twilio_sid']   ?? '';
            $token = $cfg['twilio_token'] ?? '';
            $from  = $cfg['twilio_from']  ?? 'whatsapp:+14155238886';
            if (!$sid || !$token) return ['ok'=>false,'msg'=>'Credenciales Twilio no configuradas'];

            $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_USERPWD        => "$sid:$token",
                CURLOPT_POSTFIELDS     => http_build_query([
                    'From' => $from,
                    'To'   => 'whatsapp:' . $telefono,
                    'Body' => $mensaje,
                ]),
            ]);
            $resp = curl_exec($ch);
            $err  = curl_error($ch);
            curl_close($ch);
            $json = json_decode($resp, true);
            return ['ok' => isset($json['sid']), 'msg' => $json['message'] ?? $err ?? 'Error desconocido'];

        // ── UltraMsg ──
        case 'ultramsg':
            $instance = $cfg['ultramsg_instance'] ?? '';
            $token    = $cfg['ultramsg_token']    ?? '';
            if (!$instance || !$token) return ['ok'=>false,'msg'=>'Credenciales UltraMsg no configuradas'];

            $ch = curl_init("https://api.ultramsg.com/$instance/messages/chat");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'token'  => $token,
                    'to'     => $telefono,
                    'body'   => $mensaje,
                ]),
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);
            $json = json_decode($resp, true);
            return ['ok' => ($json['sent'] ?? '') === 'true', 'msg' => $json['message'] ?? 'Error'];

        // ── 360dialog ──
        case '360dialog':
            $apiKey = $cfg['dialog360_apikey'] ?? '';
            if (!$apiKey) return ['ok'=>false,'msg'=>'API Key 360dialog no configurada'];

            $ch = curl_init('https://waba.360dialog.io/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["D360-API-KEY: $apiKey","Content-Type: application/json"],
                CURLOPT_POSTFIELDS     => json_encode([
                    'to'   => $telefono,
                    'type' => 'text',
                    'text' => ['body' => $mensaje],
                ]),
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);
            $json = json_decode($resp, true);
            return ['ok' => isset($json['messages'][0]['id']), 'msg' => $json['meta']['developer_message'] ?? 'Error'];

        // ── WhatsApp Local (whatsapp-web.js — GRATIS) ──
        case 'local_wa':
            $ch = curl_init('http://127.0.0.1:3000/send');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(['to' => $telefono, 'message' => $mensaje]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            ]);
            $resp  = curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            if ($errno || $resp === false) {
                return ['ok'=>false,'msg'=>'Servidor WhatsApp local no disponible (¿está corriendo node server.js?)'];
            }
            $json = json_decode($resp, true);
            return ['ok' => ($json['ok'] ?? false), 'msg' => $json['msg'] ?? 'Sin respuesta'];

        default:
            return ['ok'=>false,'msg'=>"Proveedor '$proveedor' no soportado"];
    }
}
