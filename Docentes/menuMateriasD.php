<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";

// 🔐 Verificar sesión EXCLUSIVA de docente
if (!isset($_SESSION['DOCENTE'])) {
    header("Location: index.php");
    exit();
}

$idProfesor = $_SESSION['DOCENTE']['idProfesor'];

// Si el grupo viene por GET
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seleccionar Materia</title>
  <link rel="stylesheet" href="assets/css/materiasD.css">
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

<a href="menuDocente.php" class="back-arrow">&#8592; Regresar</a>

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
