<?php
session_start();

// Verifica que el usuario haya iniciado sesión como profesor
if (!isset($_SESSION['correo']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../index.php");
    exit;
}

// Guardamos el correo del profesor para usar en la página
$correoProfesor = $_SESSION['correo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos</title>
    <link rel="stylesheet" href="assets/css/gruposD.css">
</head>
<body>
    <header>
        <h1>Lista de mis grupos</h1>
    </header>

    <div class="container">
        <?php if (!empty($listaGrupos)): ?>
            <?php foreach ($listaGrupos as $grupo): ?>
                <div class="card">
                    <center><h2><?= htmlspecialchars($grupo['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2></center>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($grupo['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Tutor:</strong> <?= htmlspecialchars($grupo['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <center><a class="btn" href="menuMateriasD.php?idGrupo=<?= $grupo['idGrupo']; ?>">Información del grupo</a></center>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes grupos asignados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
