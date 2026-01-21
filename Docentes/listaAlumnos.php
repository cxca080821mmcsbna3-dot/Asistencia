<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
// 🔐 Validar sesión DOCENTE
if (!isset($_SESSION['DOCENTE'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id_materia']) || !isset($_GET['idGrupo'])) {
header("Location: menuMateriasD.php");
    exit();
}

$id_materia = intval($_GET['id_materia']);
$id_grupo = intval($_GET['idGrupo']);

// Datos de la materia y grupo
$sqlMat = "SELECT m.nombre AS nombre_materia, g.nombre AS nombre_grupo
           FROM grupo_materia gm
           JOIN materias m ON gm.id_materia = m.id_materia
           JOIN grupo g ON gm.id_grupo = g.idGrupo
           WHERE gm.id_materia = :id_materia AND gm.id_grupo = :id_grupo
           LIMIT 1";
$stmt = $pdo->prepare($sqlMat);
$stmt->execute([':id_materia' => $id_materia, ':id_grupo' => $id_grupo]);
$materia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    die("❌ No se encontró la materia para este grupo.");
}

$fechaHoy = date('Y-m-d');

// --- Registrar asistencia (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    header('Content-Type: application/json; charset=utf-8');
    $id_alumno = intval($_POST['id_alumno'] ?? 0);

    if ($id_alumno <= 0) {
        echo json_encode(['ok' => false]);
        exit;
    }

    $q = $pdo->prepare("SELECT id_asistencia FROM asistencia 
                        WHERE id_alumno = :id_alumno AND id_materia = :id_materia 
                        AND fecha = :fecha LIMIT 1");
    $q->execute([':id_alumno' => $id_alumno, ':id_materia' => $id_materia, ':fecha' => $fechaHoy]);
    $exists = $q->fetchColumn();

    if ($exists) {
        $pdo->prepare("DELETE FROM asistencia WHERE id_asistencia = :id")->execute([':id' => $exists]);
        echo json_encode(['ok' => true, 'action' => 'deleted']);
    } else {
        $pdo->prepare("INSERT INTO asistencia (fecha, estado, id_alumno, id_grupo, id_materia)
                       VALUES (:fecha, 'Ausente', :id_alumno, :id_grupo, :id_materia)")
             ->execute([':fecha' => $fechaHoy, ':id_alumno' => $id_alumno, ':id_grupo' => $id_grupo, ':id_materia' => $id_materia]);
        echo json_encode(['ok' => true, 'action' => 'inserted']);
    }
    exit;
}

// --- Obtener alumnos del grupo ---
$sqlAl = "SELECT id_alumno, matricula, nombre, apellidos, numero_lista
          FROM alumno 
          WHERE id_grupo = :id_grupo 
          ORDER BY numero_lista";
$stmtAl = $pdo->prepare($sqlAl);
$stmtAl->execute([':id_grupo' => $id_grupo]);
$alumnos = $stmtAl->fetchAll(PDO::FETCH_ASSOC);

// --- Asistencias ya registradas ---
$sqlAs = "SELECT id_alumno FROM asistencia
          WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha = :fecha";
$stmtAs = $pdo->prepare($sqlAs);
$stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':fecha' => $fechaHoy]);
$rowsAs = $stmtAs->fetchAll(PDO::FETCH_COLUMN);
$inasistencias = array_flip($rowsAs);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asistencia <?= htmlspecialchars($materia['nombre_materia']) ?></title>
<style>
* {
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma;
  background: #f0e8dc;
  padding: 12px;
  display: flex;
  justify-content: center;
}

/* Contenedor */
.wrapper {
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 900px;
  position: relative;
}

/* Títulos */
h1 {
  color: #8b4513;
  margin-bottom: 5px;
}

.small {
  color: #5c4033;
  margin-bottom: 15px;
}

/* Botón regresar */
.back-arrow {
  display: inline-block;
  margin-bottom: 10px;
  background-color: #a0522d;
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  padding: 8px 14px;
  border-radius: 8px;
  transition: 0.3s;
}

.back-arrow:hover {
  background-color: #deb887;
  color: #4b2e05;
}

/* =======================
   TABLA NORMAL (PC)
   ======================= */
.table-responsive {
  width: 100%;
  overflow-x: auto;
}

table {
  border-collapse: collapse;
  width: 100%;
  min-width: 600px;
}

th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}

th {
  background: #f5deb3;
  color: #4b3621;
}

/* Botón asistencia */
.btn-cuadro {
  width: 24px;
  height: 24px;
  border: 1px solid #888;
  border-radius: 5px;
  background: #fff;
  cursor: pointer;
}

.btn-cuadro.tachado {
  background: #ff6b6b;
}

/* =======================
   MODO CELULAR
   ======================= */
@media (max-width: 600px) {

  table, thead, tbody, th, td, tr {
    display: block;
    width: 100%;
  }

  thead {
    display: none;
  }

  tr {
    background: #fff8ef;
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  }

  td {
    border: none;
    text-align: left;
    padding: 6px 0;
  }

  td::before {
    font-weight: bold;
    color: #8b4513;
    display: inline-block;
    width: 110px;
  }

  td:nth-child(1)::before { content: "No:"; }
  td:nth-child(2)::before { content: "Matrícula:"; }
  td:nth-child(3)::before { content: "Alumno:"; }
  td:nth-child(4)::before { content: "Asistencia:"; }

  .btn-cuadro {
    width: 28px;
    height: 28px;
  }
}

</style>
</head>
<body>

<div class="wrapper">
<a href="menuDocente.php" class="back-arrow">&#8592; Regresar</a>
  <h1>Asistencia: <?= htmlspecialchars($materia['nombre_materia']) ?></h1>
  <p class="small"><b>Grupo:</b> <?= htmlspecialchars($materia['nombre_grupo']) ?><br>
  <b>Fecha:</b> <?= date('d/m/Y') ?></p>
<div class="table-responsive">
  <table>

    <thead><tr><th>No.</th><th>Matrícula</th><th>Alumno</th><th>Asistencia</th></tr></thead>
    <tbody>
      <?php foreach($alumnos as $al): ?>
      <tr>
        <td><?= $al['numero_lista'] ?></td>
        <td><?= htmlspecialchars($al['matricula']) ?></td>
        <td style="text-align:left"><?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?></td>
        <td>
          <button class="btn-cuadro <?= isset($inasistencias[$al['id_alumno']])?'tachado':'' ?>"
                  data-id="<?= $al['id_alumno'] ?>"></button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
      </div>
</div>

<script>
document.querySelectorAll('.btn-cuadro').forEach(btn=>{
  btn.onclick=()=>{
    const fd=new FormData();
    fd.append('action','toggle');
    fd.append('id_alumno',btn.dataset.id);
    fetch('',{method:'POST',body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.ok) btn.classList.toggle('tachado');
      else alert('Error al actualizar');
    });
  };
});
</script>
</body>
</html>
