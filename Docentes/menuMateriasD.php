<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";

// Consultar materias
$sql = "SELECT * FROM materias";
$stmt = $pdo->query($sql);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Seleccionar Materia</title>
  <link rel="stylesheet" href="assets/css/materias.css">
</head>
<body>
  <header>
    <h1>Selecciona una Materia</h1>
  </header>

  <div class="container">
    <?php if (!empty($materias)): ?>
      <ul class="materias-lista">
        <?php foreach ($materias as $materia): ?>
          <li>
            <a href="listaAlumnos.php?id_materia=<?= $materia['id_materia'] ?>" class="btn">
              <?= htmlspecialchars($materia['nombre']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay materias registradas.</p>
    <?php endif; ?>
  </div>
</body>
</html>
