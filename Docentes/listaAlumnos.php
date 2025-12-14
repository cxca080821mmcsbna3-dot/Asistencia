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
    $tipo = ($_POST['tipo'] ?? '') === 'R' ? 'Retardo' : 'Ausente'; // R = Retardo, A = Ausente

    if ($id_alumno <= 0) {
        echo json_encode(['ok' => false]);
        exit;
    }

    // Verificar si ya existe asistencia con ese mismo estado
    $q = $pdo->prepare("SELECT id_asistencia FROM asistencia
                        WHERE id_alumno = :id AND id_materia = :m AND fecha = :f AND estado = :e");
    $q->execute([':id' => $id_alumno, ':m' => $id_materia, ':f' => $fechaHoy, ':e' => $tipo]);
    $exists = $q->fetchColumn();

    if ($exists) {
        $pdo->prepare("DELETE FROM asistencia WHERE id_asistencia = :id")
             ->execute([':id' => $exists]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Eliminar si existe el otro estado (para no duplicar)
    $pdo->prepare("DELETE FROM asistencia WHERE id_alumno = :id AND id_materia = :m AND fecha = :f")
        ->execute([':id' => $id_alumno, ':m' => $id_materia, ':f' => $fechaHoy]);

    // Insertar nuevo estado
    $pdo->prepare("INSERT INTO asistencia (fecha, estado, id_alumno, id_grupo, id_materia)
                   VALUES (:f, :e, :id, :g, :m)")
         ->execute([':f' => $fechaHoy, ':e' => $tipo, ':id' => $id_alumno, ':g' => $id_grupo, ':m' => $id_materia]);

    echo json_encode(['ok' => true]);
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


// --- Marcar inasistencias ya registradas ---
$sqlAs = "SELECT id_alumno FROM asistencia
          WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha = :fecha AND estado = 'Ausente'";
$stmtAs = $pdo->prepare($sqlAs);
$stmtAs->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':fecha' => $fechaHoy]);
$inasistencias = array_flip($stmtAs->fetchAll(PDO::FETCH_COLUMN));


// --- Marcar retardos ya registrados ---
$sqlRe = "SELECT id_alumno FROM asistencia
          WHERE id_grupo = :id_grupo AND id_materia = :id_materia AND fecha = :fecha AND estado = 'Retardo'";
$stmtRe = $pdo->prepare($sqlRe);
$stmtRe->execute([':id_grupo' => $id_grupo, ':id_materia' => $id_materia, ':fecha' => $fechaHoy]);
$retardos = array_flip($stmtRe->fetchAll(PDO::FETCH_COLUMN));
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Asistencia <?= htmlspecialchars($materia['nombre_materia']) ?></title>
<style>
body{font-family:Segoe UI;background:#f0e8dc;padding:15px;display:flex;justify-content:center;}
.wrapper{background:#fff;border-radius:12px;padding:1rem;box-shadow:0 0 10px rgba(0,0,0,0.2);}
h1{color:#8b4513;margin-bottom:5px;}
.small{color:#5c4033;margin-bottom:20px;}
table{border-collapse:collapse;width:100%;min-width:650px;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
th{background:#f5deb3;color:#4b3621;}
.btn-cuadro{width:22px;height:22px;border:1px solid #888;border-radius:4px;background:#fff;cursor:pointer;}
.btn-cuadro.tachado{background:#ff6b6b;}
.btn-cuadro.naranja{background:orange;}
.retardo{border-color:#d88b00;}
.back-arrow{display:inline-block;margin-bottom:10px;text-decoration:none;color:#a0522d;font-weight:bold;}
</style>
</head>
<body>
<a href="menuDocente.php" class="back-arrow">&#8592; Regresar</a>
<div class="wrapper">
  <h1>Asistencia: <?= htmlspecialchars($materia['nombre_materia']) ?></h1>
  <p class="small"><b>Grupo:</b> <?= htmlspecialchars($materia['nombre_grupo']) ?><br>
  <b>Fecha:</b> <?= date('d/m/Y') ?></p>

  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>Matrícula</th>
        <th>Alumno</th>
        <th>Inasistencia</th>
        <th>Retardo</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($alumnos as $al): ?>
      <tr>
        <td><?= $al['numero_lista'] ?></td>
        <td><?= htmlspecialchars($al['matricula']) ?></td>
        <td style="text-align:left"><?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?></td>

        <!-- Inasistencia -->
        <td>
          <button class="btn-cuadro <?= isset($inasistencias[$al['id_alumno']])?'tachado':'' ?>"
                  data-id="<?= $al['id_alumno'] ?>" data-type="A"></button>
        </td>

        <!-- Retardo -->
        <td>
          <button class="btn-cuadro retardo <?= isset($retardos[$al['id_alumno']])?'naranja':'' ?>"
                  data-id="<?= $al['id_alumno'] ?>" data-type="R"></button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
document.querySelectorAll('.btn-cuadro').forEach(btn=>{
  btn.onclick=()=>{
    const fd = new FormData();
    fd.append('action','toggle');
    fd.append('id_alumno', btn.dataset.id);
    fd.append('tipo', btn.dataset.type);

    fetch('', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{
      if(!d.ok) return alert('Error al actualizar');

      if(btn.dataset.type === 'A'){ // Inasistencia
        btn.classList.toggle('tachado');
        const ret = btn.parentNode.nextElementSibling.querySelector('.btn-cuadro');
        ret.classList.remove('naranja');
      } else { // Retardo
        btn.classList.toggle('naranja');
        const ina = btn.parentNode.previousElementSibling.querySelector('.btn-cuadro');
        ina.classList.remove('tachado');
      }
    });
  };
});
</script>
</body>
</html>
