<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

// --- Validar materia y grupo ---
if (!isset($_GET['idMateria']) || !isset($_GET['idGrupo'])) {
    header("Location: materias.php");
    exit();
}

$id_materia = intval($_GET['idMateria']);
$id_grupo   = intval($_GET['idGrupo']);

$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
if ($mes < 1 || $mes > 12) $mes = intval(date('m'));
$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// ---------- Obtener materia ----------
$sqlMat = "SELECT m.nombre AS nombre_materia, g.nombre AS nombre_grupo
           FROM grupo_materia mg
           JOIN materias m ON mg.id_materia = m.id_materia
           JOIN grupo g ON mg.id_grupo = g.idGrupo
           WHERE mg.id_materia = :id_materia AND mg.id_grupo = :id_grupo
           LIMIT 1";
$stmt = $pdo->prepare($sqlMat);
$stmt->execute([':id_materia' => $id_materia, ':id_grupo' => $id_grupo]);
$materia = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$materia) die("La materia no está asignada a este grupo.");

// ---------- Exportar a Excel ----------
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=asistencia_{$materia['nombre_materia']}_{$materia['nombre_grupo']}_{$anio}-{$mes}.xls");

    $stmtA = $pdo->prepare("SELECT * FROM alumno WHERE id_grupo = :id_grupo ORDER BY numero_lista ASC");
    $stmtA->execute([':id_grupo' => $id_grupo]);
    $alumnosExp = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    $likeMes = sprintf("%04d-%02d%%", $anio, $mes);
    $stmtAs = $pdo->prepare("SELECT id_alumno, fecha FROM asistencia
                             WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes");
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
        echo "<td>" . htmlspecialchars($al['numero_lista']) . "</td>";
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

// ---------- Consultar alumnos ----------
$sqlAl = "SELECT id_alumno, matricula, nombre, apellidos, numero_lista
          FROM alumno
          WHERE id_grupo = :id_grupo
          ORDER BY numero_lista ASC";
$stmtAl = $pdo->prepare($sqlAl);
$stmtAl->execute([':id_grupo' => $id_grupo]);
$alumnos = $stmtAl->fetchAll(PDO::FETCH_ASSOC);

// ---------- Consultar asistencias ----------
$likeMes = sprintf("%04d-%02d%%", $anio, $mes);
$stmtAs = $pdo->prepare("SELECT id_alumno, fecha FROM asistencia
                         WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes");
$stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':mes' => $likeMes]);
$rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);

$inasistencias = [];
foreach ($rowsAs as $r) {
    $d = intval(date('d', strtotime($r['fecha'])));
    $inasistencias[$r['id_alumno']][$d] = true;
}

// ---------- Meses en español ----------
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Asistencia - <?= htmlspecialchars($materia['nombre_materia']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
html, body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f0e8dc;
  display: flex;
  justify-content: center;
  padding: 7px;
}
.wrapper {
  position: relative;
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 1.2rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 100%;
  overflow-x: auto;
}
h1 {
  font-size: 1.6rem;
  color: #8b4513;
  text-shadow: 1px 1px #f5deb3;
  margin: 0;
}
.small {
  font-size: 1rem;
  color: #5c4033;
  margin-bottom: 1.5rem;
}
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
  color: #5c4033;
}
.controls button {
  padding: 0.6rem 1rem;
  background-color: #deb887;
  color: #3b2f2f;
  border: 1px solid #a0522d;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}
.controls button:hover {
  background-color: #d2b48c;
}
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
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 1200px;
  background-color: #fff;
  border-radius: 8px;
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
}
.alumno-col {
  text-align: left;
  padding-left: 10px;
}
.btn-cuadro {
  width: 20px;
  height: 20px;
  border: 1px solid #888;
  border-radius: 4px;
  background: #fff;
}
.btn-cuadro.tachado {
  background: #ff6b6b;
}
.back-arrow {
  position: absolute;
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
</head>
<body>

<div class="wrapper">
    <a href="materias.php" class="back-arrow">&#8592; Regresar</a>
    <h1>Asistencia: <?= htmlspecialchars($materia['nombre_materia']) ?></h1>
    <p class="small"><strong>Grupo:</strong> <?= htmlspecialchars($materia['nombre_grupo']) ?></p>

    <div class="controls">
        <form method="get">
            <input type="hidden" name="idMateria" value="<?= $id_materia ?>">
            <input type="hidden" name="idGrupo" value="<?= $id_grupo ?>">
            <label>Mes:</label>
            <select name="mes">
                <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>><?= $meses[$m] ?></option>
                <?php endfor; ?>
            </select>
            <label>Año:</label>
            <input type="number" name="anio" value="<?= $anio ?>" style="width:90px">
            <button type="submit">Ver</button>
        </form>

        <div style="margin-left:auto;">
            <a class="export-btn" href="?idMateria=<?= $id_materia ?>&idGrupo=<?= $id_grupo ?>&mes=<?= $mes ?>&anio=<?= $anio ?>&export=excel">Descargar Excel</a>
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
                        <td><?= htmlspecialchars($al['numero_lista']) ?></td>
                        <td><?= htmlspecialchars($al['matricula']) ?></td>
                        <td class="alumno-col"><?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?></td>
                        <?php for ($d=1;$d<=$diasMes;$d++): 
                            $marcada = isset($inasistencias[$al['id_alumno']][$d]); ?>
                            <td><div class="btn-cuadro <?= $marcada ? 'tachado' : '' ?>"></div></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
