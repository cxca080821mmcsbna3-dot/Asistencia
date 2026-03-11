<?php
session_start();
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sin autorización']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$proveedoresValidos = ['callmebot','twilio','ultramsg','360dialog','local_wa'];

if (!in_array($data['proveedor'] ?? '', $proveedoresValidos)) {
    echo json_encode(['ok'=>false,'msg'=>'Proveedor no válido']); exit;
}

$config = [
    'activo'    => (bool)($data['activo'] ?? false),
    'proveedor' => $data['proveedor'],
    // CallMeBot
    'callmebot_apikey'     => trim($data['callmebot_apikey']     ?? ''),
    // Twilio
    'twilio_sid'           => trim($data['twilio_sid']           ?? ''),
    'twilio_token'         => trim($data['twilio_token']         ?? ''),
    'twilio_from'          => trim($data['twilio_from']          ?? 'whatsapp:+14155238886'),
    // UltraMsg
    'ultramsg_instance'    => trim($data['ultramsg_instance']    ?? ''),
    'ultramsg_token'       => trim($data['ultramsg_token']       ?? ''),
    // 360dialog
    'dialog360_apikey'     => trim($data['dialog360_apikey']     ?? ''),
    // Plantillas de mensajes
    'plantilla_falta'      => trim($data['plantilla_falta']      ?? 'Estimado padre de familia, le informamos que el alumno {nombre} no se presentó el día {fecha}. — CECYTEM'),
    'plantilla_tardanza'   => trim($data['plantilla_tardanza']   ?? 'Estimado padre de familia, el alumno {nombre} llegó con tardanza el día {fecha} a las {hora}. — CECYTEM'),
    'plantilla_incidencia' => trim($data['plantilla_incidencia'] ?? 'Estimado padre de familia, el alumno {nombre} presentó la siguiente incidencia el {fecha}: {incidencia}. — CECYTEM'),
];

$file = __DIR__ . '/../config/whatsapp.json';
if (file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['ok'=>true,'msg'=>'Configuración guardada correctamente']);
} else {
    echo json_encode(['ok'=>false,'msg'=>'No se pudo escribir el archivo de configuración']);
}
