<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

// Verificación de sesión del alumno
if (!isset($_SESSION['matricula'])) {
    header("Location: ../index.php");
    exit();
}

$matricula = $_SESSION['matricula'];

// Buscar los datos completos del alumno
$sql = "SELECT id_alumno, numero_lista, matricula, nombre, apellidos, telefono, id_grupo 
        FROM alumno WHERE matricula = :matricula LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':matricula' => $matricula]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) die("Alumno no encontrado.");

$id_alumno = $alumno['id_alumno'];
$id_grupo  = $alumno['id_grupo'];

// Obtener lista de materias
$sqlMat = "SELECT m.id_materia, m.nombre 
           FROM grupo_materia gm
           JOIN materias m ON gm.id_materia = m.id_materia
           WHERE gm.id_grupo = :id_grupo";
$stmtMat = $pdo->prepare($sqlMat);
$stmtMat->execute([':id_grupo' => $id_grupo]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

// Materia seleccionada
$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : ($materias[0]['id_materia'] ?? 0);

// Mes y año seleccionados
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
if ($mes < 1 || $mes > 12) $mes = intval(date('m'));

// Cantidad de días del mes
$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// Consultar asistencias del alumno
$likeMes = sprintf("%04d-%02d%%", $anio, $mes);
$sqlAs = "SELECT fecha 
          FROM asistencia 
          WHERE id_alumno = :id_alumno 
            AND id_materia = :id_materia 
            AND fecha LIKE :mes";
$stmtAs = $pdo->prepare($sqlAs);
$stmtAs->execute([
    ':id_alumno' => $id_alumno,
    ':id_materia' => $id_materia,
    ':mes' => $likeMes
]);
$rowsAs = $stmtAs->fetchAll(PDO::FETCH_ASSOC);

// Crear arreglo con inasistencias
$inasistencias = [];
foreach ($rowsAs as $r) {
    $d = intval(date('d', strtotime($r['fecha'])));
    $inasistencias[$d] = true;
}

// Días de la semana en español
$diasSemanaES = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asistencia de <?= htmlspecialchars($alumno['nombre']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="css/asistencia.css?v=2.1">
</head>
<body>
<a href="menu_alumno.php" class="back-arrow">&#8592; Regresar</a>

<div class="wrapper">
  <h1>Asistencia: <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></h1>
  <p class="small"><strong>Matrícula:</strong> <?= htmlspecialchars($alumno['matricula']) ?></p>

  <form method="get" class="filtro">
    <input type="hidden" name="id_materia" value="<?= $id_materia ?>">
    
    <label>Materia:</label>
    <select name="id_materia" onchange="this.form.submit()">
      <?php foreach ($materias as $m): ?>
        <option value="<?= $m['id_materia'] ?>" <?= $m['id_materia']==$id_materia?'selected':'' ?>>
          <?= htmlspecialchars($m['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Mes:</label>
    <select name="mes">
      <?php for ($m=1;$m<=12;$m++): ?>
        <option value="<?= $m ?>" <?= $m==$mes?'selected':'' ?>><?= strftime('%B', mktime(0,0,0,$m,1)) ?></option>
      <?php endfor; ?>
    </select>

    <label>Año:</label>
    <input type="number" name="anio" value="<?= $anio ?>" style="width:90px">
    <button type="submit">Ver</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Día</th>
        <th>Día de la semana</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($d = 1; $d <= $diasMes; $d++):
        $fechaDia = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
        $numeroDiaSemana = date('w', strtotime($fechaDia));
        $diaSemana = $diasSemanaES[$numeroDiaSemana];
        $clase = isset($inasistencias[$d]) ? 'ausente' : 'presente';
      ?>
      <tr>
        <td><?= $d ?></td>
        <td><?= $diaSemana ?></td>
        <td class="asistencia <?= $clase ?>"><?= isset($inasistencias[$d]) ? '❌ Ausente' : '✅ Presente' ?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("modo") === "oscuro") {
    document.body.classList.add("dark-mode");
  }
});
</script>
</body>
</html>
