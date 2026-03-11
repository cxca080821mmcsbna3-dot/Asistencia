<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sin autorización']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$tabla  = $data['tabla']  ?? '';
$id     = intval($data['id'] ?? 0);
$accion = $data['accion'] ?? 'editar';

if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }

try {
    if ($tabla === 'diaria') {

        if ($accion === 'eliminar') {
            $stmt = $pdo->prepare("SELECT id_asistencia_diaria FROM asistencia_diaria WHERE id_asistencia_diaria = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) { echo json_encode(['ok'=>false,'msg'=>'Registro no encontrado']); exit; }
            $pdo->prepare("DELETE FROM asistencia_diaria WHERE id_asistencia_diaria = ?")->execute([$id]);
            echo json_encode(['ok'=>true,'msg'=>'Registro eliminado']);

        } elseif ($accion === 'editar') {
            $estadosValidos = ['Presente','Tardío'];
            $nuevoEstado    = $data['estado']       ?? '';
            $nuevaHora      = $data['hora_entrada'] ?? '';
            $observaciones  = $data['observaciones'] ?? null;
            $incidencia     = isset($data['incidencia']) && $data['incidencia'] !== ''
                              ? trim($data['incidencia']) : null;

            if (!in_array($nuevoEstado, $estadosValidos)) {
                echo json_encode(['ok'=>false,'msg'=>'Estado inválido']); exit;
            }
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $nuevaHora)) {
                echo json_encode(['ok'=>false,'msg'=>'Formato de hora inválido (HH:MM)']); exit;
            }
            if (strlen($nuevaHora) === 5) $nuevaHora .= ':00';

            $pdo->prepare("
                UPDATE asistencia_diaria
                SET estado       = ?,
                    hora_entrada = ?,
                    observaciones= ?,
                    incidencia   = ?,
                    dispositivo  = CONCAT(dispositivo, ' [editado]')
                WHERE id_asistencia_diaria = ?
            ")->execute([$nuevoEstado, $nuevaHora, $observaciones, $incidencia, $id]);

            echo json_encode(['ok'=>true,'msg'=>'Registro actualizado']);
        }

    } elseif ($tabla === 'materia') {

        if ($accion === 'eliminar') {
            $stmt = $pdo->prepare("SELECT id_asistencia FROM asistencia WHERE id_asistencia = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) { echo json_encode(['ok'=>false,'msg'=>'Registro no encontrado']); exit; }
            $pdo->prepare("DELETE FROM asistencia WHERE id_asistencia = ?")->execute([$id]);
            echo json_encode(['ok'=>true,'msg'=>'Registro eliminado']);

        } elseif ($accion === 'editar') {
            $estadosValidos = ['Ausente','Retardo','Justificante'];
            $nuevoEstado    = $data['estado'] ?? '';
            if (!in_array($nuevoEstado, $estadosValidos)) {
                echo json_encode(['ok'=>false,'msg'=>'Estado inválido']); exit;
            }
            $pdo->prepare("UPDATE asistencia SET estado = ? WHERE id_asistencia = ?")->execute([$nuevoEstado, $id]);
            echo json_encode(['ok'=>true,'msg'=>'Registro actualizado']);
        }

    } else {
        echo json_encode(['ok'=>false,'msg'=>'Tabla no válida']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error BD: '.$e->getMessage()]);
}
