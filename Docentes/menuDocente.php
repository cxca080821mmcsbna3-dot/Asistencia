<?php
session_start();

// Si no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['idProfesor'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . "../assets/sentenciasSQL/gruposD.php";

$grupos = new Grupos();
$idProfesor = $_SESSION['idProfesor'];

// ✅ Solo los grupos que el profesor tiene asignados
$listaGrupos = $grupos->leerGruposPorProfesor($idProfesor);
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
                    <h2><?= htmlspecialchars($grupo['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($grupo['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Tutor:</strong> <?= htmlspecialchars($grupo['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn" href="menuMateriasD.php?idGrupo=<?= $grupo['idGrupo']; ?>">Información del grupo</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes grupos asignados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
