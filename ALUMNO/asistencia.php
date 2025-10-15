<?php
require __DIR__ . '/../assets/sentenciasSQL/Conexion.php';

session_start();

// Bloqueo: solo alumnos
if (!isset($_SESSION['idAlumno']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../index.php");
    exit;
}

$idAlumno = $_SESSION['idAlumno'];

try {
    // Trae solo las asistencias del alumno logueado
    $stmt = $pdo->prepare("
        SELECT fecha, estado, id_materia 
        FROM asistencia 
        WHERE id_alumno = :idAlumno
    ");
    $stmt->execute(['idAlumno' => $idAlumno]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error al consultar la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Asistencias</title>
    <link rel="stylesheet" href="css/asistencia.css?v=2.1">
</head>
<body>

<!-- Flecha para regresar al menú -->
<a href="menu_alumno.php" class="back-arrow">&#8592; Regresar</a>

<h1>Registro de Asistencias</h1>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Materia</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($asistencias) > 0): ?>
            <?php foreach ($asistencias as $fila): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['fecha']) ?></td>
                    <td>
                        <?php
                            $estado = strtolower($fila['estado']);
                            $clase = ($estado === 'presente') ? 'presente' :
                                     (($estado === 'ausente') ? 'ausente' : 'tarde');
                        ?>
                        <span class="estado <?= $clase ?>"><?= ucfirst($fila['estado']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($fila['id_materia']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No hay registros de asistencia</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
document.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("modo") === "oscuro") {
    document.body.classList.add("dark-mode");
  }
});
</script>
</body>
</html>
