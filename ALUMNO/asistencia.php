<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

/* 🔐 Validar sesión */
if (!isset($_SESSION['ALUMNO'])) {
    header("Location: ../index.php");
    exit;
}

/* 📌 Matrícula */
$matricula = $_SESSION['ALUMNO']['matricula'];

/* 🔎 Obtener alumno */
$stmt = $pdo->prepare("
    SELECT id_alumno, id_grupo 
    FROM alumno 
    WHERE matricula = :matricula
    LIMIT 1
");
$stmt->execute([':matricula' => $matricula]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    die("Alumno no encontrado.");
}

$id_alumno = $alumno['id_alumno'];
$id_grupo  = $alumno['id_grupo'];

/* 📚 Materias */
$stmtMat = $pdo->prepare("
    SELECT DISTINCT m.id_materia, m.nombre
    FROM materias m
    JOIN grupo_materia gm ON gm.id_materia = m.id_materia
    WHERE gm.id_grupo = :grupo
    ORDER BY m.nombre
");
$stmtMat->execute([':grupo' => $id_grupo]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

/* 📅 Fecha actual */
$mesActual  = (int) date('m');
$anioActual = (int) date('Y');

/* 📘 Materia seleccionada */
$id_materia = intval($_GET['idMateria'] ?? ($materias[0]['id_materia'] ?? 0));

/* 📅 Mes y año solicitados */
$mes  = intval($_GET['mes'] ?? $mesActual);
$anio = intval($_GET['anio'] ?? $anioActual);

/* 🚫 Bloquear meses y años futuros */
if ($anio > $anioActual || ($anio == $anioActual && $mes > $mesActual)) {
    $mes  = $mesActual;
    $anio = $anioActual;
}

$mes  = ($mes < 1 || $mes > 12) ? $mesActual : $mes;
$anio = ($anio < 2000 || $anio > $anioActual) ? $anioActual : $anio;

$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

/* 📊 Asistencias */
$likeMes = sprintf("%04d-%02d%%", $anio, $mes);

$stmtAs = $pdo->prepare("
    SELECT fecha, estado 
    FROM asistencia
    WHERE id_alumno = :alumno
      AND id_materia = :materia
      AND fecha LIKE :mes
");
$stmtAs->execute([
    ':alumno'  => $id_alumno,
    ':materia' => $id_materia,
    ':mes'     => $likeMes
]);

$asistencias = [];
foreach ($stmtAs as $r) {
    $dia = (int) date('d', strtotime($r['fecha']));
    $asistencias[$dia] = $r['estado'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis asistencias</title>
<link rel="stylesheet" href="css/asistencia.css">
</head>

<body>

<div class="wrapper">
<a href="menu_alumno.php" class="back-arrow">&#8592; Regresar</a>
<h2>Mis asistencias</h2>

<!-- 🔍 FILTROS -->
<div class="controls">
<form method="get">

<label><strong>Materia:</strong></label>
<select name="idMateria" onchange="this.form.submit()">
<?php foreach ($materias as $m): ?>
    <option value="<?= $m['id_materia'] ?>" <?= $m['id_materia']==$id_materia?'selected':'' ?>>
        <?= htmlspecialchars($m['nombre']) ?>
    </option>
<?php endforeach; ?>
</select>

<label>Mes:</label>
<select name="mes">
<?php for ($i=1; $i<=12; $i++): ?>
    <?php
        if ($anio == $anioActual && $i > $mesActual) continue;
    ?>
    <option value="<?= $i ?>" <?= $i==$mes?'selected':'' ?>><?= $i ?></option>
<?php endfor; ?>
</select>

<label>Año:</label>
<input type="number" name="anio" value="<?= $anio ?>" max="<?= $anioActual ?>" style="width:80px">

<button type="submit">Ver</button>
</form>
</div>

<br>

<!-- 📅 TABLA -->
<div class="table-container">
<table>

<tr>
<?php for ($d=1; $d<=$diasMes; $d++): ?>
    <th><?= $d ?></th>
<?php endfor; ?>
</tr>

<tr>
<?php for ($d=1; $d<=$diasMes; $d++):
    $fechaActualDia = sprintf("%04d-%02d-%02d", $anio, $mes, $d);
    $diaSemana = date('N', strtotime($fechaActualDia));

    if ($diaSemana == 6) {
        $estado = 'Sábado';
        $color  = '#e0e0e0';
    } elseif ($diaSemana == 7) {
        $estado = 'Domingo';
        $color  = '#e0e0e0';
    } else {
        $estado = $asistencias[$d] ?? '';
        $color = $estado === 'Ausente' ? '#ff6b6b'
               : ($estado === 'Retardo' ? '#ffa500'
               : ($estado === 'Justificante' ? '#4da6ff' : '#fff'));
    }
?>
<td style="background:<?= $color ?>; font-size:12px">
    <?= htmlspecialchars($estado) ?>
</td>
<?php endfor; ?>
</tr>

</table>
</div>
</div>

</body>
</html>
