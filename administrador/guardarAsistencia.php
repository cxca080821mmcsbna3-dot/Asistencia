<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

$data = json_decode(file_get_contents("php://input"), true);
$cambios = $data["cambios"] ?? [];

if(!$cambios){
    echo "No hay cambios";
    exit;
}

foreach($cambios as $c){
    $id_alumno = intval($c["alumno"]);
    $fecha = $c["fecha"];
    $estado = $c["estado"];
    $id_grupo = $_SESSION["id_grupo_asistencia"];
    $id_materia = $_SESSION["id_materia_asistencia"];

    // Verificar si ya existe registro
    $check = $pdo->prepare("SELECT id_asistencia FROM asistencia 
        WHERE id_alumno = ? AND fecha = ? AND id_grupo = ? AND id_materia = ? LIMIT 1");
    $check->execute([$id_alumno, $fecha, $id_grupo, $id_materia]);

    if ($check->rowCount() > 0) {
        // Actualizar
        $id_asistencia = $check->fetchColumn();
        $upd = $pdo->prepare("UPDATE asistencia SET estado = ? WHERE id_asistencia = ?");
        $upd->execute([$estado, $id_asistencia]);

    } else {
        // Insertar
        $ins = $pdo->prepare("INSERT INTO asistencia (id_alumno, id_grupo, id_materia, fecha, estado) 
                              VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$id_alumno, $id_grupo, $id_materia, $fecha, $estado]);
    }
}

echo "ok";
