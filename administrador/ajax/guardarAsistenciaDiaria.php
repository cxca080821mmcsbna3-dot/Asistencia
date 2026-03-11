<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sin autorización']); exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$id_alumno = intval($data['id_alumno'] ?? 0);
if (!$id_alumno) { echo json_encode(['ok'=>false,'msg'=>'Alumno inválido']); exit; }

$hoy    = date('Y-m-d');
$hora   = date('H:i:s');
$horaHM = substr($hora, 0, 5);

// ── Incidencia desde la solicitud (enviada por la página según ?modo) ──
$incidencia = trim($data['incidencia'] ?? '') ?: null;

// ── Leer configuración de horarios ──
$configFile = __DIR__ . '/../config/horarios.json';
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : null;

$estado         = 'Presente';
$turnoNombre    = 'Sin turno';
$turnoDetectado = false;

if ($config) {
    $turnosActivos = [];
    foreach (['matutino','vespertino'] as $key) {
        $t = $config[$key] ?? null;
        if ($t && ($t['activo'] ?? false)) $turnosActivos[$key] = $t;
    }
    $claves = array_keys($turnosActivos);
    foreach ($claves as $i => $key) {
        $t      = $turnosActivos[$key];
        $inicio = $t['horaInicio'] ?? '00:00';
        $limite = $t['horaLimite'] ?? '23:59';
        $nextKey  = $claves[$i + 1] ?? null;
        $finTurno = $nextKey
            ? date('H:i', strtotime($turnosActivos[$nextKey]['horaInicio']) - 60)
            : '23:59';
        if ($horaHM >= $inicio && $horaHM <= $finTurno) {
            $turnoNombre    = $t['nombre'] ?? ucfirst($key);
            $turnoDetectado = true;
            $estado = ($horaHM <= $limite) ? 'Presente' : 'Tardío';
            break;
        }
    }
}

$dispositivo = ($data['dispositivo'] ?? 'Manual') . ' | ' . $turnoNombre;

try {
    $stmtI = $pdo->prepare("
        INSERT INTO asistencia_diaria
            (id_alumno, fecha, hora_entrada, estado, dispositivo, incidencia)
        VALUES
            (:id_alumno, :fecha, :hora, :estado, :dispositivo, :incidencia)
        ON DUPLICATE KEY UPDATE
            estado       = VALUES(estado),
            hora_entrada = VALUES(hora_entrada),
            dispositivo  = VALUES(dispositivo),
            incidencia   = VALUES(incidencia)
    ");
    $stmtI->execute([
        ':id_alumno'   => $id_alumno,
        ':fecha'       => $hoy,
        ':hora'        => $hora,
        ':estado'      => $estado,
        ':dispositivo' => $dispositivo,
        ':incidencia'  => $incidencia,
    ]);

    $accion = ($stmtI->rowCount() === 1) ? 'insertado' : 'actualizado';

    $pdo->prepare("
        INSERT INTO asistencia_diaria_logs
            (id_alumno, fecha_intento, hora_intento, resultado, mensaje)
        VALUES (?, ?, ?, 'Éxito', ?)
    ")->execute([$id_alumno, $hoy, $hora,
        "$accion | estado:$estado | turno:$turnoNombre | incidencia:".($incidencia ?? 'ninguna')
    ]);

    echo json_encode([
        'ok'             => true,
        'accion'         => $accion,
        'hora'           => $hora,
        'estado'         => $estado,
        'turno'          => $turnoNombre,
        'turnoDetectado' => $turnoDetectado,
        'incidencia'     => $incidencia,
    ]);

} catch (PDOException $e) {
    // Intentar añadir columna si no existe (primera vez)
    if (str_contains($e->getMessage(), "Unknown column 'incidencia'")) {
        try {
            $pdo->exec("ALTER TABLE asistencia_diaria ADD COLUMN incidencia VARCHAR(120) DEFAULT NULL");
            // Reintentar
            $pdo->prepare("
                INSERT INTO asistencia_diaria
                    (id_alumno, fecha, hora_entrada, estado, dispositivo, incidencia)
                VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                    estado=VALUES(estado), hora_entrada=VALUES(hora_entrada),
                    dispositivo=VALUES(dispositivo), incidencia=VALUES(incidencia)
            ")->execute([$id_alumno,$hoy,$hora,$estado,$dispositivo,$incidencia]);
            echo json_encode(['ok'=>true,'accion'=>'insertado','hora'=>$hora,'estado'=>$estado,
                              'turno'=>$turnoNombre,'turnoDetectado'=>$turnoDetectado,'incidencia'=>$incidencia]);
            exit;
        } catch (PDOException $e2) {
            http_response_code(500);
            echo json_encode(['ok'=>false,'msg'=>$e2->getMessage()]); exit;
        }
    }
    try { $pdo->prepare("INSERT INTO asistencia_diaria_logs (id_alumno,fecha_intento,hora_intento,resultado,mensaje) VALUES(?,?,?,'Error',?)")
              ->execute([$id_alumno,$hoy,$hora,$e->getMessage()]); } catch(Exception $ig){}
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error al guardar: '.$e->getMessage()]);
}
