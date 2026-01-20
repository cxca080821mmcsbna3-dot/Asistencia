<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

header('Content-Type: application/json');
error_reporting(0); // evita warnings que rompen el JSON

if (!isset($_GET['semestre'])) {
    echo json_encode([]);
    exit;
}

$semestreNumero = intval($_GET['semestre']);

$mapaSemestres = [
    1 => 'Primer semestre',
    2 => 'Segundo semestre',
    3 => 'Tercer semestre',
    4 => 'Cuarto semestre',
    5 => 'Quinto semestre',
    6 => 'Sexto semestre'
];

if (!isset($mapaSemestres[$semestreNumero])) {
    echo json_encode([]);
    exit;
}

$semestreTexto = $mapaSemestres[$semestreNumero];

$stmt = $pdo->prepare(
    "SELECT id_materia, nombre 
     FROM materias 
     WHERE semestre = ?"
);
$stmt->execute([$semestreTexto]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
