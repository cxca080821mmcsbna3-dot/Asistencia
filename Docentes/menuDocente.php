<?php
require_once __DIR__. "../assets/sentenciasSQL/gruposD.php";
$grupos =new Grupos();

$listaGrupos = $grupos->leerGrupos();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos</title>
    <link rel="stylesheet" href="./assets/css/gruposD.css">
    
</head>
<body>
    <header>
    <h1>Lista de Grupos</h1>
    
    </header>

    <div class="container">
        <?php if (!empty($listaGrupos)): ?>
            <?php foreach ($listaGrupos as $grupos): ?>
                <div class="card">

                    <h2><?= htmlspecialchars($grupos['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><strong>Descripcion:</strong> <?= htmlspecialchars($grupos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Tutor:</strong> <?= htmlspecialchars($grupos['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn" href="menuMateriasD.php">Informacion del grupo</a>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay grupos registrados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
