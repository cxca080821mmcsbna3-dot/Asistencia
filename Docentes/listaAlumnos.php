<?php
// asistencia.php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php"; // debe exponer $pdo

// Requerir id_materia en GET
if (!isset($_GET['id_materia'])) {
    header("Location: menuMaterias.php");
    exit();
}
$id_materia = intval($_GET['id_materia']);

// Obtener mes y año seleccionados (por GET). Default: mes actual
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
if ($mes < 1 || $mes > 12) $mes = intval(date('m'));

$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// ---------- Obtener materia y su grupo ----------
$sqlMat = "SELECT m.*, g.nombre AS nombre_grupo, g.idGrupo
           FROM materias m
           JOIN grupo g ON m.idGrupo = g.idGrupo
           WHERE m.id_materia = :id_materia
           LIMIT 1";
$stmt = $pdo->prepare($sqlMat);
$stmt->execute([':id_materia' => $id_materia]);
$materia = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$materia) {
    die("Materia no encontrada.");
}
$id_grupo = intval($materia['idGrupo']);

// ---------- AJAX: Toggle inasistencia ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    header('Content-Type: application/json; charset=utf-8');

    $id_alumno = intval($_POST['id_alumno'] ?? 0);
    $dia = intval($_POST['dia'] ?? 0);
    $mesPost = intval($_POST['mes'] ?? $mes);
    $anioPost = intval($_POST['anio'] ?? $anio);

    if ($id_alumno <= 0 || $dia < 1 || $dia > 31) {
        echo json_encode(['ok' => false, 'msg' => 'Parámetros inválidos']);
        exit;
    }

    $fecha = sprintf('%04d-%02d-%02d', $anioPost, $mesPost, $dia);

    // Verificar si ya existe la inasistencia para ese alumno, materia, grupo y fecha
    $q = $pdo->prepare("SELECT id_asistencia FROM asistencia
                       WHERE id_alumno = :id_alumno
                         AND id_grupo = :id_grupo
                         AND id_materia = :id_materia
                         AND fecha = :fecha
                       LIMIT 1");
    $q->execute([
        ':id_alumno' => $id_alumno,
        ':id_grupo' => $id_grupo,
        ':id_materia' => $id_materia,
        ':fecha' => $fecha
    ]);
    $exists = $q->fetchColumn();

    if ($exists) {
        // Borrar (desmarcar)
        $pdo->prepare("DELETE FROM asistencia WHERE id_asistencia = :id")->execute([':id' => $exists]);
        echo json_encode(['ok' => true, 'action' => 'deleted', 'fecha' => $fecha]);
        exit;
    } else {
        // Insertar (marcar Ausente)
        $pdo->prepare("INSERT INTO asistencia (fecha, estado, id_alumno, id_grupo, id_materia)
                       VALUES (:fecha, 'Ausente', :id_alumno, :id_grupo, :id_materia)")
            ->execute([
                ':fecha' => $fecha,
                ':id_alumno' => $id_alumno,
                ':id_grupo' => $id_grupo,
                ':id_materia' => $id_materia
            ]);
        echo json_encode(['ok' => true, 'action' => 'inserted', 'fecha' => $fecha]);
        exit;
    }
}

// ---------- Exportar a Excel ----------
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=asistencia_{$materia['nombre']}_{$anio}-{$mes}.xls");

    $sqlA = "SELECT * FROM alumno WHERE id_grupo = :id_grupo ORDER BY apellidos, nombre";
    $stmtA = $pdo->prepare($sqlA);
    $stmtA->execute([':id_grupo' => $id_grupo]);
    $alumnosExp = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    $likeMes = sprintf("%04d-%02d%%", $anio, $mes);
    $sqlAs = "SELECT id_alumno, fecha FROM asistencia
              WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes";
    $stmtAs = $pdo->prepare($sqlAs);
    $stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':mes' => $likeMes]);
    $rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);
    $inas = [];
    foreach ($rowsAs as $r) {
        $d = intval(date('d', strtotime($r['fecha'])));
        $inas[$r['id_alumno']][$d] = true;
    }

    echo "<table border='1'><tr><th>No.</th><th>Matrícula</th><th>Alumno</th>";
    for ($d = 1; $d <= $diasMes; $d++) echo "<th>$d</th>";
    echo "</tr>";

    foreach ($alumnosExp as $i => $al) {
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>" . htmlspecialchars($al['matricula']) . "</td>";
        echo "<td>" . htmlspecialchars($al['apellidos'] . ' ' . $al['nombre']) . "</td>";
        for ($d = 1; $d <= $diasMes; $d++) {
            echo "<td>" . (isset($inas[$al['id_alumno']][$d]) ? "X" : "") . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

// ---------- Consultar alumnos del grupo de esta materia ----------
$sqlAl = "SELECT id_alumno, matricula, nombre, apellidos, numero_lista
          FROM alumno
          WHERE id_grupo = :id_grupo
          ORDER BY numero_lista ASC";
$stmtAl = $pdo->prepare($sqlAl);
$stmtAl->execute([':id_grupo' => $id_grupo]);
$alumnos = $stmtAl->fetchAll(PDO::FETCH_ASSOC);


// ---------- Consultar asistencias del mes ----------
$likeMes = sprintf("%04d-%02d%%", $anio, $mes);
$sqlAs = "SELECT id_alumno, fecha FROM asistencia
          WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes";
$stmtAs = $pdo->prepare($sqlAs);
$stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':mes' => $likeMes]);
$rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);

$inasistencias = [];
foreach ($rowsAs as $r) {
    $d = intval(date('d', strtotime($r['fecha'])));
    $inasistencias[$r['id_alumno']][$d] = true;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Asistencia - <?= htmlspecialchars($materia['nombre']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* --- Estilo base --- */
html, body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f0e8dc; /* fondo suave, sin imagen */
  display: flex;
  justify-content: center;
  padding: 7px;
}

/* --- Contenedor principal --- */
.wrapper {
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 0.7rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  max-width: 100%;
  width: 100%;
  overflow-x: auto;
}

/* --- Encabezado --- */
h1 {
  font-size: 1.6rem;
  color: #8b4513;
  text-shadow: 1px 1px #f5deb3;
  margin-bottom: 0.3rem;
}

.small {
  font-size: 1rem;
  color: #5c4033;
  margin-bottom: 1.5rem;
}

/* --- Controles superiores --- */
.controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.controls form {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.controls label {
  font-weight: bold;
  color: #4b3621;
}

select,
input[type="number"] {
  padding: 0.5rem 0.8rem;
  border: 1px solid #c2a88c;
  border-radius: 6px;
  background-color: rgba(255, 250, 240, 0.95);
  font-size: 1rem;
  font-family: 'Georgia', serif;
  color: #5c4033;
}

select:focus,
input[type="number"]:focus {
  border-color: #8b4513;
  box-shadow: 0 0 5px rgba(139, 69, 19, 0.3);
  outline: none;
}

.controls button {
  padding: 0.6rem 1rem;
  background-color: #deb887;
  color: #3b2f2f;
  border: 1px solid #a0522d;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-family: 'Georgia', serif;
}

.controls button:hover {
  background-color: #d2b48c;
}

/* --- Botón de exportar --- */
.export-btn {
  background: #deb887;
  color: #fff;
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
}

.export-btn:hover {
  background-color: #3b2f2f;
}

/* --- Tabla de asistencia --- */
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 1200px; /* hace la tabla más larga en horizontal */
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

th, td {
  border: 1px solid #cfcfcf;
  padding: 8px;
  text-align: center;
}

th {
  background-color: #f5deb3;
  color: #5c4033;
  font-weight: bold;
}

.alumno-col {
  text-align: left;
  padding-left: 10px;
}

/* --- Botones de asistencia --- */
.btn-cuadro {
  width: 20px;
  height: 20px;
  border: 1px solid #888;
  background: #fff;
  cursor: pointer;
  border-radius: 4px;
}

.btn-cuadro.tachado {
  background: #ff6b6b;
}

/* --- Responsive design --- */
@media (max-width: 768px) {
  .controls {
    flex-direction: column;
    align-items: flex-start;
  }

  .controls form {
    flex-direction: column;
    align-items: flex-start;
  }

  table {
    font-size: 12px;
    min-width: 1000px;
  }

  th, td {
    padding: 5px;
  }
}
</style>


</head>
<body>
<div class="wrapper">
    <h1>Asistencia: <?= htmlspecialchars($materia['nombre']) ?></h1>
    <p class="small"><strong>Grupo:</strong> <?= htmlspecialchars($materia['nombre_grupo']) ?></p>

    <div class="controls">
        <form id="filtro" method="get" style="display:flex;align-items:center;gap:6px">
            <input type="hidden" name="id_materia" value="<?= $id_materia ?>">
            <label>Mes:</label>
            <select name="mes">
                <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
            <label>Año:</label>
            <input type="number" name="anio" value="<?= $anio ?>" style="width:90px">
            <button type="submit">Ver</button>
        </form>

        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
            <a class="export-btn" href="?id_materia=<?= $id_materia ?>&mes=<?= $mes ?>&anio=<?= $anio ?>&export=excel">Descargar Excel</a>
        </div>
    </div>

    <div style="overflow:auto">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Matrícula</th>
                <th class="alumno-col">Alumno</th>
                <?php for ($d = 1; $d <= $diasMes; $d++): ?>
                    <th><?= $d ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
  <?php foreach ($alumnos as $al): ?>
    <tr>
      <td class="numero"><?= htmlspecialchars($al['numero_lista']) ?></td>
      <td><?= htmlspecialchars($al['matricula']) ?></td>
      <td class="nombre"><?= htmlspecialchars($al['apellidos'] . ' ' . $al['nombre']) ?></td>
      <?php for ($d = 1; $d <= $diasMes; $d++): 
          $marcada = isset($inasistencias[$al['id_alumno']][$d]);
      ?>
        <td>
          <button
            class="btn-cuadro <?= $marcada ? 'tachado' : '' ?>"
            data-id-alumno="<?= $al['id_alumno'] ?>"
            data-dia="<?= $d ?>"
            title="<?= $marcada ? 'Quitar inasistencia' : 'Marcar inasistencia' ?>">
          </button>
        </td>
      <?php endfor; ?>
    </tr>
  <?php endforeach; ?>
</tbody>

    </table>
    </div>
</div>

<script>
const idMateria = <?= $id_materia ?>;
const mesSel = <?= $mes ?>;
const anioSel = <?= $anio ?>;
document.querySelectorAll('.btn-cuadro').forEach(btn => {
    btn.addEventListener('click', () => {
        const idAlumno = btn.getAttribute('data-id-alumno');
        const dia = btn.getAttribute('data-dia');
        const fd = new FormData();
        fd.append('action', 'toggle');
        fd.append('id_alumno', idAlumno);
        fd.append('dia', dia);
        fd.append('mes', mesSel);
        fd.append('anio', anioSel);

        fetch('', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(resp => {
            if (resp.ok) btn.classList.toggle('tachado');
            else alert('Error: ' + (resp.msg || 'No se pudo procesar'));
        })
        .catch(e => alert('Error de red: ' + e));
    });
});
</script>
</body>
</html>
