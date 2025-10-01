<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";

// Verificar si se seleccionó materia
if (!isset($_GET['id_materia'])) {
    header("Location: menuMaterias.php");
    exit();
}

$id_materia = intval($_GET['id_materia']);

// Guardar inasistencia
if (isset($_POST['inasistencia'])) {
    $id_alumno = intval($_POST['id_alumno']);
    $id_grupo = intval($_POST['id_grupo']);

    $fecha = date("Y-m-d");
    $estado = "Inasistencia";

    $sql = "INSERT INTO asistencia (fecha, estado, id_alumno, id_grupo, id_materia) 
            VALUES (:fecha, :estado, :id_alumno, :id_grupo, :id_materia)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fecha' => $fecha,
        ':estado' => $estado,
        ':id_alumno' => $id_alumno,
        ':id_grupo' => $id_grupo,
        ':id_materia' => $id_materia
    ]);
}

// Consultar alumnos de esa materia usando la relación con grupos
$sql = "SELECT a.*
        FROM alumno a
        INNER JOIN grupo_materia mg ON a.id_grupo = mg.id_grupo
        WHERE mg.id_materia = :id_materia";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_materia' => $id_materia]);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar nombre de la materia
$sqlMateria = "SELECT nombre FROM materias WHERE id_materia = :id_materia";
$stmtMat = $pdo->prepare($sqlMateria);
$stmtMat->execute([':id_materia' => $id_materia]);
$materiaNombre = $stmtMat->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos - <?= htmlspecialchars($materiaNombre) ?></title>
    <link rel="stylesheet" href="assets/css/alumnos.css">
</head>
<body>
    <header>
        <h1>Alumnos de <?= htmlspecialchars($materiaNombre) ?></h1>
     
    </header>

    <div class="container">
        <?php if (!empty($alumnos)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Domicilio</th>
                        <th>Grupo</th>
                        <th>Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?= htmlspecialchars($alumno['nombre']) ?></td>
                        <td><?= htmlspecialchars($alumno['apellidos']) ?></td>
                        <td><?= htmlspecialchars($alumno['correo']) ?></td>
                        <td><?= htmlspecialchars($alumno['telefono']) ?></td>
                        <td><?= htmlspecialchars($alumno['domicilio']) ?></td>
                        <td><?= htmlspecialchars($alumno['id_grupo']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id_alumno" value="<?= $alumno['id_alumno'] ?>">
                                <input type="hidden" name="id_grupo" value="<?= $alumno['id_grupo'] ?>">
                                <button type="submit" name="inasistencia" class="btn-inasistencia">Marcar Inasistencia</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay alumnos registrados en esta materia.</p>
        <?php endif; ?>
    </div>
</body>
</html>
