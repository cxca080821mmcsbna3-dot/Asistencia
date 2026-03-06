<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(['error'=>'Sin autorización']); exit;
}

$hoy = date('Y-m-d');

try {
    // Alumnos que NO tienen registro en asistencia_diaria hoy
    $stmt = $pdo->prepare("
        SELECT al.id_alumno, al.matricula, al.nombre, al.apellidos,
               al.numero_lista, g.nombre AS nombre_grupo
        FROM alumno al
        LEFT JOIN grupo g ON al.id_grupo = g.idGrupo
        WHERE al.id_alumno NOT IN (
            SELECT id_alumno FROM asistencia_diaria WHERE fecha = :hoy
        )
        ORDER BY g.nombre ASC, al.numero_lista ASC
        LIMIT 500
    ");
    $stmt->execute([':hoy' => $hoy]);
    $faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total de alumnos en el sistema
    $total = $pdo->query("SELECT COUNT(*) FROM alumno")->fetchColumn();

    echo json_encode([
        'ok'        => true,
        'faltantes' => $faltantes,
        'total'     => (int)$total,
        'fecha'     => $hoy,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
