<?php
/**
 * wa-proxy.php
 * Proxy PHP → Node.js server (localhost:3000)
 * El servidor Node.js solo acepta peticiones desde 127.0.0.1.
 * Este archivo es el puente seguro entre el navegador y Node.js.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok'=>false,'msg'=>'Sin autorización']);
    exit;
}

$action  = $_GET['action'] ?? $_POST['action'] ?? '';
$nodeUrl = 'http://127.0.0.1:3000';

// Mapa de acciones permitidas
$rutas = [
    'status' => ['GET',  '/status'],
    'qr'     => ['GET',  '/qr'],
    'send'   => ['POST', '/send'],
    'logout' => ['POST', '/logout'],
];

if (!isset($rutas[$action])) {
    echo json_encode(['ok'=>false,'msg'=>'Acción no válida']); exit;
}

[$metodo, $ruta] = $rutas[$action];

$ch = curl_init($nodeUrl . $ruta);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_CONNECTTIMEOUT => 3,
]);

if ($metodo === 'POST') {
    $body = file_get_contents('php://input') ?: '{}';
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
}

$resp  = curl_exec($ch);
$errno = curl_errno($ch);
$http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno || $resp === false) {
    // Node.js no está corriendo
    echo json_encode([
        'ok'     => false,
        'estado' => 'servidor_off',
        'msg'    => 'El servidor Node.js no está corriendo en localhost:3000',
    ]);
    exit;
}

// Pasar respuesta tal cual
http_response_code($http);
echo $resp;
