<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Sin autorización']);
    exit;
}

$matricula = trim($_GET['matricula'] ?? '');
if ($matricula === '') {
    echo json_encode(['error' => 'Matrícula vacía']);
    exit;
}

try {
    // matricula es bigint — se compara como número
    $stmt = $pdo->prepare("
        SELECT a.id_alumno, a.matricula, a.nombre, a.apellidos,
               a.curp, a.telefono, a.id_grupo, a.numero_lista,
               g.nombre AS nombre_grupo
        FROM alumno a
        LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
        WHERE a.matricula = :matricula
        LIMIT 1
    ");
    $stmt->execute([':matricula' => $matricula]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) {
        echo json_encode(['error' => 'Alumno no encontrado']);
        exit;
    }

    $hoy = date('Y-m-d');

    // ── asistencia_diaria: registro de hoy ──
    // UNIQUE KEY (id_alumno, fecha) garantiza max 1 por día
    $stmtHoy = $pdo->prepare("
        SELECT id_asistencia_diaria, estado, hora_entrada
        FROM asistencia_diaria
        WHERE id_alumno = :id AND fecha = :fecha
        LIMIT 1
    ");
    $stmtHoy->execute([':id' => $alumno['id_alumno'], ':fecha' => $hoy]);
    $registroHoy = $stmtHoy->fetch(PDO::FETCH_ASSOC);

    // ── asistencia_diaria: últimos 5 registros ──
    $stmtHist = $pdo->prepare("
        SELECT fecha, estado, hora_entrada
        FROM asistencia_diaria
        WHERE id_alumno = :id
        ORDER BY fecha DESC
        LIMIT 5
    ");
    $stmtHist->execute([':id' => $alumno['id_alumno']]);
    $historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

    // ── asistencia_diaria: conteos globales ──
    $stmtStats = $pdo->prepare("
        SELECT
            COUNT(CASE WHEN estado = 'Presente' THEN 1 END) AS presentes,
            COUNT(CASE WHEN estado = 'Tardío'   THEN 1 END) AS tardios,
            COUNT(*) AS total_dias
        FROM asistencia_diaria
        WHERE id_alumno = :id
    ");
    $stmtStats->execute([':id' => $alumno['id_alumno']]);
    $statsDiaria = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // ── asistencia (por materia): faltas acumuladas ──
    // No se toca nada de la tabla original, solo se lee
    $stmtFaltas = $pdo->prepare("
        SELECT COUNT(*) AS faltas_materia
        FROM asistencia
        WHERE id_alumno = :id AND estado = 'Ausente'
    ");
    $stmtFaltas->execute([':id' => $alumno['id_alumno']]);
    $faltas = $stmtFaltas->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'alumno'      => $alumno,
        'registroHoy' => $registroHoy ?: null,
        'historial'   => $historial,
        'stats'       => [
            'presentes'      => (int)($statsDiaria['presentes']   ?? 0),
            'tardios'        => (int)($statsDiaria['tardios']     ?? 0),
            'total_dias'     => (int)($statsDiaria['total_dias']  ?? 0),
            'faltas_materia' => (int)($faltas['faltas_materia']   ?? 0),
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
}
