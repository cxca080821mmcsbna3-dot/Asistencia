<?php
session_start();
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Sin autorización']);
    exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$id_alumno = intval($data['id_alumno'] ?? 0);
$estado    = $data['estado']    ?? '';
$dispositivo = $data['dispositivo'] ?? 'Manual';

// Solo los estados que acepta el ENUM de asistencia_diaria
$estadosValidos = ['Presente', 'Tardío'];
if (!$id_alumno || !in_array($estado, $estadosValidos)) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

$hoy  = date('Y-m-d');
$hora = date('H:i:s');

try {
    // ── Intentar INSERT ──
    // UNIQUE KEY (id_alumno, fecha) en asistencia_diaria previene duplicados a nivel BD
    $stmtI = $pdo->prepare("
        INSERT INTO asistencia_diaria
            (id_alumno, fecha, hora_entrada, estado, dispositivo)
        VALUES
            (:id_alumno, :fecha, :hora, :estado, :dispositivo)
        ON DUPLICATE KEY UPDATE
            estado       = VALUES(estado),
            hora_entrada = VALUES(hora_entrada),
            dispositivo  = VALUES(dispositivo)
    ");
    $stmtI->execute([
        ':id_alumno'   => $id_alumno,
        ':fecha'       => $hoy,
        ':hora'        => $hora,
        ':estado'      => $estado,
        ':dispositivo' => $dispositivo,
    ]);

    $accion = ($stmtI->rowCount() === 1) ? 'insertado' : 'actualizado';

    // ── Registrar en logs ──
    $stmtLog = $pdo->prepare("
        INSERT INTO asistencia_diaria_logs
            (id_alumno, fecha_intento, hora_intento, resultado, mensaje)
        VALUES
            (:id, :fecha, :hora, 'Éxito', :msg)
    ");
    $stmtLog->execute([
        ':id'   => $id_alumno,
        ':fecha'=> $hoy,
        ':hora' => $hora,
        ':msg'  => $accion . ' — estado: ' . $estado,
    ]);

    echo json_encode([
        'ok'     => true,
        'accion' => $accion,
        'hora'   => $hora,
        'estado' => $estado,
    ]);

} catch (PDOException $e) {
    // Registrar error en logs si se puede
    try {
        $pdo->prepare("
            INSERT INTO asistencia_diaria_logs
                (id_alumno, fecha_intento, hora_intento, resultado, mensaje)
            VALUES (?, ?, ?, 'Error', ?)
        ")->execute([$id_alumno, $hoy, $hora, $e->getMessage()]);
    } catch (Exception $ignored) {}

    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $e->getMessage()]);
}
