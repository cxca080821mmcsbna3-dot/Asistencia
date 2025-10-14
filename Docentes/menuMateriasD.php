<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";

// Verificar que haya sesión de profesor
if (!isset($_SESSION['idProfesor'])) {
    header("Location: index.php");
    exit();
}

$idProfesor = $_SESSION['idProfesor'];

// Si el grupo viene por GET (ej. de gruposD.php)
if (!isset($_GET['idGrupo'])) {
    echo "No se especificó un grupo.";
    exit();
}

$idGrupo = intval($_GET['idGrupo']);

// Consultar materias del profesor en este grupo
$sql = "
    SELECT m.*
    FROM materias m
    INNER JOIN grupo_materia gm 
        ON m.id_materia = gm.id_materia
    WHERE gm.id_profesor = :idProfesor
      AND gm.id_grupo = :idGrupo
    ORDER BY m.nombre ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':idProfesor' => $idProfesor,
    ':idGrupo' => $idGrupo
]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Seleccionar Materia</title>
  <link rel="stylesheet" href="assets/css/materiasD.css">
</head>
<body>
  <header>
    <h1>Selecciona una Materia</h1>
  </header>

  <div class="container">
    <?php if (!empty($materias)): ?>
      <div class="materias-grid">
  <?php foreach ($materias as $materia): ?>
    <div class="card">
      <h2><?= htmlspecialchars($materia['nombre']) ?></h2>
      <a href="listaAlumnos.php?id_materia=<?= $materia['id_materia'] ?>&idGrupo=<?= $idGrupo ?>" class="btn">
        Tomar asistencia
      </a>
    </div>
  <?php endforeach; ?>
</div>

    <?php else: ?>
      <p>No hay materias registradas para este grupo y profesor.</p>
    <?php endif; ?>
  </div>
</body>
</html>
