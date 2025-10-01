<?php
session_start();
if (!isset($_SESSION['idAdmin']) && !isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once '../assets/sentenciasSQL/Docentes.php';
$docente = new Docentes();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID de docente no válido'); window.location.href = 'docentes.php';</script>";
    exit;
}

$id = $_GET['id'];
$profe = $docente->obtenerPorId($id);

if (!$profe) {
    echo "<script>alert('Docente no encontrado'); window.location.href = 'docentes.php';</script>";
    exit;
}

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $telefono  = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $correo    = $_POST['correo'];

    $actualizado = $docente->actualizar($id, $nombre, $apellidos, $telefono, $domicilio, $correo);

    if ($actualizado) {
        echo "<script>alert('Docente actualizado con éxito'); window.location.href = 'docentes.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar docente');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Docente</title>
</head>
<body>
    <h2>Editar Docente</h2>
    <form method="post">
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($profe['nombre']) ?>" required><br>

        <label for="apellidos">Apellidos:</label><br>
        <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($profe['apellidos']) ?>" required><br>

        <label for="telefono">Teléfono:</label><br>
        <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($profe['telefono']) ?>"><br>

        <label for="domicilio">Domicilio:</label><br>
        <input type="text" name="domicilio" id="domicilio" value="<?= htmlspecialchars($profe['domicilio']) ?>"><br>

        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($profe['correo']) ?>"><br><br>

        <input type="submit" value="Actualizar">
        <a href="docentes.php"><button type="button">Cancelar</button></a>
    </form>
</body>
</html>
