<?php
session_start();
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sin autorización']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit; }

// Validar estructura básica
$turnos = ['matutino','vespertino'];
foreach ($turnos as $t) {
    if (!isset($data[$t])) { echo json_encode(['ok'=>false,'msg'=>"Falta turno: $t"]); exit; }
    $hi = $data[$t]['horaInicio'] ?? '';
    $hl = $data[$t]['horaLimite'] ?? '';
    // Validar formato HH:MM
    if (!preg_match('/^\d{2}:\d{2}$/', $hi) || !preg_match('/^\d{2}:\d{2}$/', $hl)) {
        echo json_encode(['ok'=>false,'msg'=>"Formato de hora inválido en $t"]); exit;
    }
    if ($hl <= $hi) {
        echo json_encode(['ok'=>false,'msg'=>"La hora límite debe ser después de la hora de inicio en $t"]); exit;
    }
}

$file = __DIR__ . '/../config/horarios.json';
$guardado = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($guardado === false) {
    echo json_encode(['ok'=>false,'msg'=>'No se pudo guardar el archivo de configuración']);
    exit;
}

echo json_encode(['ok'=>true, 'msg'=>'Configuración guardada correctamente']);
