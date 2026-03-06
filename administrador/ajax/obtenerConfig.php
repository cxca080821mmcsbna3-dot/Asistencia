<?php
session_start();
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['error'=>'Sin autorización']); exit;
}

$file = __DIR__ . '/../config/horarios.json';
if (!file_exists($file)) {
    // Defaults si no existe el archivo
    $default = [
        'matutino'   => ['activo'=>true,  'horaInicio'=>'07:00', 'horaLimite'=>'08:10', 'nombre'=>'Matutino'],
        'vespertino' => ['activo'=>true,  'horaInicio'=>'13:00', 'horaLimite'=>'14:10', 'nombre'=>'Vespertino'],
    ];
    file_put_contents($file, json_encode($default));
    echo json_encode(['ok'=>true, 'config'=>$default]);
    exit;
}

$config = json_decode(file_get_contents($file), true);
echo json_encode(['ok'=>true, 'config'=>$config]);
