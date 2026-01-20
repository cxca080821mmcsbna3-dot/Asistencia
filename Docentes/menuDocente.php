<?php
session_start();
// No me cambien nada de aqui >:P 😄

// 🔐 Validar sesión EXCLUSIVA de docente
if (!isset($_SESSION['DOCENTE'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . "../assets/sentenciasSQL/gruposD.php";

$grupos = new Grupos();

// 🔑 Obtener id del profesor desde su sesión
$idProfesor = $_SESSION['DOCENTE']['idProfesor'];

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
<style>
    .back-arrow {

  top: 12px;
  right: 15px;
  background-color: #a0522d;
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  padding: 8px 14px;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}
.back-arrow:hover {
  background-color: #deb887;
  color: #4b2e05;
}
    </style>
<body>

    <a href="cerrar.php" class="back-arrow">&#8592; cerrar sesion</a>

    <header>
        <h1>Lista de mis grupos</h1>
    </header>

    <div class="container">
        <?php if (!empty($listaGrupos)): ?>
            <?php foreach ($listaGrupos as $grupo): ?>
                <div class="card">
                    <center>
                        <h2><?= htmlspecialchars($grupo['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </center>
                    <p><strong>Semestre:</strong> <?= htmlspecialchars($grupo['semestre'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Tutor:</strong> <?= htmlspecialchars($grupo['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <center>
                        <a class="btn" href="menuMateriasD.php?idGrupo=<?= $grupo['idGrupo']; ?>">
                            Información del grupo
                        </a>
                    </center>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes grupos asignados.</p>
        <?php endif; ?>
    </div>

</body>
</html>
