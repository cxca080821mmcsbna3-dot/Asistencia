<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";

// --- Validar materia y grupo ---
if (!isset($_GET['idMateria']) || !isset($_GET['idGrupo'])) {
    header("Location: materias.php");
    exit();
}

$id_materia = intval($_GET['idMateria']);
$id_grupo   = intval($_GET['idGrupo']);
$_SESSION["idMateria"] = $id_materia;
$_SESSION["idGrupo"] = $id_grupo;


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

    $stmtA = $pdo->prepare("SELECT id_alumno, matricula, nombre, apellidos FROM alumno WHERE id_grupo = :id_grupo ORDER BY id_alumno ASC");
    $stmtA->execute([':id_grupo' => $id_grupo]);
    $alumnosExp = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    $likeMes = sprintf("%04d-%02d%%", $anio, $mes);
    $stmtAs = $pdo->prepare("SELECT id_alumno, fecha, estado FROM asistencia
                             WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes");
    $stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':mes' => $likeMes]);
    $rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);

    $inas = [];
    foreach ($rowsAs as $r) {
        $d = intval(date('d', strtotime($r['fecha'])));
        $inas[$r['id_alumno']][$d] = $r['estado'];
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
            echo "<td>" . (isset($inas[$al['id_alumno']][$d]) ? $inas[$al['id_alumno']][$d] : "") . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

// ---------- Consultar alumnos ----------
$sqlAl = "SELECT id_alumno, matricula, nombre, apellidos, telefono
          FROM alumno
          WHERE id_grupo = :id_grupo
          ORDER BY id_alumno ASC";
$stmtAl = $pdo->prepare($sqlAl);
$stmtAl->execute([':id_grupo' => $id_grupo]);
$alumnos = $stmtAl->fetchAll(PDO::FETCH_ASSOC);

// 📊 NUEVO: Obtener inasistencias totales por alumno en esta materia
$alumnosConInasistencias = [];
foreach ($alumnos as $alumno) {
    $inasistenciasMateria = obtenerInasistenciasPorMateria($pdo, $alumno['id_alumno'], $id_materia);
    $alumno['inasistencias'] = $inasistenciasMateria;
    $alumnosConInasistencias[] = $alumno;
}
$alumnos = $alumnosConInasistencias;

// ---------- Consultar asistencias ----------
$likeMes = sprintf("%04d-%02d%%", $anio, $mes);
$stmtAs = $pdo->prepare("SELECT id_alumno, fecha, estado FROM asistencia
                         WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha LIKE :mes");
$stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':mes' => $likeMes]);
$rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);

$inasistencias = [];
foreach ($rowsAs as $r) {
    $d = intval(date('d', strtotime($r['fecha'])));
    $inasistencias[$r['id_alumno']][$d] = $r['estado'];
}

// ---------- Contar retardos por alumno (mes actual) ----------
$retardosPorAlumno = [];

$stmtRet = $pdo->prepare("
    SELECT id_alumno, COUNT(*) AS total
    FROM asistencia
    WHERE id_grupo = :id_grupo
      AND id_materia = :id_materia
      AND estado = 'Retardo'
      AND fecha LIKE :mes
    GROUP BY id_alumno
");

$stmtRet->execute([
    ':id_grupo'   => $id_grupo,
    ':id_materia'=> $id_materia,
    ':mes'       => $likeMes
]);

while ($r = $stmtRet->fetch()) {
    $retardosPorAlumno[$r['id_alumno']] = $r['total'];
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
}
.back-arrow:hover {
  background-color: #deb887;
  color: #4b2e05;
}
</style>
</head>
<body>

<div class="wrapper">
    <a href="materias.php?idGrupo=<?= $id_grupo ?>" class="back-arrow">&#8592; Regresar</a>
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
<div style="display:flex; align-items:center; gap:10px; font-size:14px; margin-left:20px;">
    <span style="display:flex; align-items:center; gap:4px;">
        <div style="width:18px; height:18px; background:#ff6b6b; border:1px solid #888;"></div> Ausente
    </span>
    <span style="display:flex; align-items:center; gap:4px;">
        <div style="width:18px; height:18px; background:#ffa500; border:1px solid #888;"></div> Retardo
    </span>
    <span style="display:flex; align-items:center; gap:4px;">
        <div style="width:18px; height:18px; background:#4da6ff; border:1px solid #888;"></div> Justificante
    </span>
</div>
        <div style="margin-left:auto;">
            <a class="export-btn" href="?idMateria=<?= $id_materia ?>&idGrupo=<?= $id_grupo ?>&mes=<?= $mes ?>&anio=<?= $anio ?>&export=excel">Descargar Excel</a>
        </div>

        <button id="btnEditar" style="padding:8px 12px; background:#a0522d; color:white; border-radius:6px; border:1px solid #5c4033; cursor:pointer;">Editar</button>
        <button id="btnGuardar" style="display:none; padding:8px 12px; background:#2e8b57; color:white; border-radius:6px; border:1px solid #1e5f3a; cursor:pointer;">Guardar cambios</button>
    </div>

    <div style="overflow:auto">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Matrícula</th>
                    <th>Mensaje</th>
                    <th class="alumno-col">Alumno</th>
                    <th style="background-color: #ffcccc; color: #8b0000;">⚠️ Inasist.</th>
                    <?php for ($d = 1; $d <= $diasMes; $d++): ?>
                        <th><?= $d ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alumnos as $indice => $al): ?>
                    <tr>
                        <td><?= ($indice + 1) ?></td>
                        <td><?= htmlspecialchars($al['matricula']) ?></td>
                        <td class="alumno-col">
                            <a href="detalleInasistencias.php?idAlumno=<?= $al['id_alumno'] ?>&idMateria=<?= $id_materia ?>" style="text-decoration: none; color: #4b3621; font-weight: bold;">
                                <?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?>
                            </a>
                        </td>
                        <td style="background-color: <?= $al['inasistencias'] > 0 ? '#ffebee' : '#f0f0f0' ?>;">
                            <strong style="color: <?= $al['inasistencias'] > 0 ? '#d32f2f' : '#4caf50' ?>;">
                                <?= $al['inasistencias'] ?>
                            </strong>
                        </td>
                        <?php for ($d=1;$d<=$diasMes;$d++): 
                            $estado = $inasistencias[$al['id_alumno']][$d] ?? "";
                            $color = $estado == "Ausente" ? "#ff6b6b" :
                                     ($estado == "Retardo" ? "#ffa500" :
                                     ($estado == "Justificante" ? "#4da6ff" : "#fff"));
                        ?>
                            <td>
                                <div class="btn-cuadro"
                                     data-idalumno="<?= $al['id_alumno'] ?>"
                                     data-fecha="<?= sprintf('%04d-%02d-%02d', $anio, $mes, $d) ?>"
                                     data-estado="<?= $estado ?>"
                                     style="background: <?= $color ?>"></div>
                            </td>
                        <?php endfor; ?>
                        <td>
<td>
<?php
    $idAlumno = $al['id_alumno'];
    $retardos = $retardosPorAlumno[$idAlumno] ?? 0;

    if ($retardos >= 3):

        // Teléfono desde BD (solo números)
        $telefono = preg_replace('/[^0-9]/', '', $al['telefono']);

        // Agregar código país si no lo tiene
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        $mensaje = urlencode(
            "*Buen día señor padre de familia*. Se le informa que su hijo {$al['apellidos']} {$al['nombre']} ha acumulado {$retardos} *retardos* en el mes."
        );

        $linkWhats = "https://wa.me/{$telefono}?text={$mensaje}";
?>
    <a href="<?= $linkWhats ?>"
       target="_blank"
       style="
        background:#25D366;
        color:white;
        padding:6px 10px;
        border-radius:6px;
        text-decoration:none;
        font-size:12px;
        font-weight:bold;">
        Mensaje
    </a>
<?php endif; ?>
</td>

</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
let modoEdicion = false;
let cambios = [];

const colores = {
  "Ausente": "#ff6b6b",
  "Retardo": "#ffa500",
  "Justificante": "#4da6ff"
};

// Activar edición
document.getElementById("btnEditar").onclick = () => {
  modoEdicion = true;
  document.getElementById("btnGuardar").style.display = "inline-block";

  const btnEditar = document.getElementById("btnEditar");
  btnEditar.textContent = "Cancelar";
  btnEditar.style.background = "#8b0000";

  btnEditar.onclick = () => location.reload();
};

// Click en cuadritos
document.querySelectorAll("td div.btn-cuadro").forEach(cuadro => {
  cuadro.onclick = () => {
    if (!modoEdicion) return;

    let estado = cuadro.dataset.estado || "Ausente";
    let siguiente =
      estado === "Ausente" ? "Retardo" :
      estado === "Retardo" ? "Justificante" :
      "Ausente";

    cuadro.dataset.estado = siguiente;
    cuadro.style.background = colores[siguiente];

    cambios.push({
      alumno: cuadro.dataset.idalumno,
      fecha: cuadro.dataset.fecha,
      estado: siguiente
    });
  };
});

// Guardar cambios
document.getElementById("btnGuardar").onclick = () => {
  if (cambios.length === 0) return;

  fetch("guardarAsistencia.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({cambios})
  })
  .then(r => r.text())
  .then(() => location.reload());
};
</script>


</body>
</html>
