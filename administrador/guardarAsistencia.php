<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["cambios"]) || empty($data["cambios"])) {
    echo "Sin cambios";
    exit;
}

foreach ($data["cambios"] as $cambio) {
    $idAlumno = $cambio["alumno"];
    $fecha = $cambio["fecha"];
    $estado = $cambio["estado"];
    $idMateria = $_SESSION["idMateria"];
    $idGrupo   = $_SESSION["idGrupo"];

    // Verificar si ya existe registro
    $sqlCheck = "SELECT id_asistencia FROM asistencia 
                 WHERE id_alumno = ? AND fecha = ? AND id_materia = ? AND id_grupo = ?";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([$idAlumno, $fecha, $idMateria, $idGrupo]);

    if ($stmt->rowCount() > 0) {
        // ACTUALIZAR
        $sqlUpdate = "UPDATE asistencia SET estado = ? 
                      WHERE id_alumno = ? AND fecha = ? AND id_materia = ? AND id_grupo = ?";
        $pdo->prepare($sqlUpdate)->execute([$estado, $idAlumno, $fecha, $idMateria, $idGrupo]);
    } else {
        // INSERTAR
        $sqlInsert = "INSERT INTO asistencia (id_alumno, fecha, estado, id_materia, id_grupo)
                      VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sqlInsert)->execute([$idAlumno, $fecha, $estado, $idMateria, $idGrupo]);
    }
}

echo "OK";
