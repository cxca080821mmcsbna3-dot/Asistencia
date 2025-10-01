<?php
session_start();
if (!isset($_SESSION['idAdmin']) && !isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once '../assets/sentenciasSQL/Docentes.php';
$docente = new Docentes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $telefono  = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $correo    = $_POST['correo'];

    $alta = $docente->darAltaProfe($nombre, $apellidos, $telefono, $domicilio, $correo);
    $mensaje = $alta ? "Docente dado de alta correctamente" : "Error al dar de alta al docente";
}

$listaDocentes = $docente->obtenerTodos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Docentes</title>
    <link rel="stylesheet" href="../assets/css/docentes.css">
</head>
<body>
    <a href="../menuGrupos.php"><button>Volver</button></a>

    <div class="container">
    <?php if (isset($mensaje)) echo "<script>alert('$mensaje');</script>"; ?>

    <h2>Dar de Alta a un Docente</h2>
    <form action="docentes.php" method="post">
        <input type="text" name="nombre" placeholder="Nombre" required><br>
        <input type="text" name="apellidos" placeholder="Apellidos" required><br>
        <input type="text" name="telefono" placeholder="Teléfono"><br>
        <input type="text" name="domicilio" placeholder="Domicilio"><br>
        <input type="email" name="correo" placeholder="Correo"><br>
        <input type="submit" value="Guardar">
    </form>

    <h2>Lista de Docentes</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Domicilio</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listaDocentes as $profe): ?>
                <tr>
                    <td><?= htmlspecialchars($profe['nombre'] . ' ' . $profe['apellidos']) ?></td>
                    <td><?= htmlspecialchars($profe['telefono']) ?></td>
                    <td><?= htmlspecialchars($profe['domicilio']) ?></td>
                    <td><?= htmlspecialchars($profe['correo']) ?></td>
                    <td>
                        <a href="editarDocente.php?id=<?= $profe['id_profesor'] ?>">Editar</a> |
                        <a href="eliminarDocente.php?id=<?= $profe['id_profesor'] ?>" onclick="return confirm('¿Estás seguro de eliminar este docente?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>
</html>
