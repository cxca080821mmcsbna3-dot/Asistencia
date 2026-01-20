<?php
require_once __DIR__ . "/../assets/sentenciasSQL/grupos.php";

session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

$gruposObj = new Grupos();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $idEliminar = intval($_POST['idGrupo']);
    if ($gruposObj->eliminarGrupo($idEliminar)) {
        echo "<script>alert('Evento eliminado correctamente'); window.location='gruposCreados.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el evento');</script>";
    }
}

/* ------------------ FILTROS DE PERIODO ------------------ */
$periodo1 = isset($_GET['p1']) ? true : false;
$periodo2 = isset($_GET['p2']) ? true : false;

$listaGrupos = $gruposObj->leerGrupos();

if ($periodo1 || $periodo2) {

    $listaGrupos = array_filter($listaGrupos, function ($g) use ($periodo1, $periodo2) {

        $p1 = ['Primer semestre', 'Tercer semestre', 'Quinto semestre'];
        $p2 = ['Segundo semestre', 'Cuarto semestre', 'Sexto semestre'];

        if ($periodo1 && in_array($g['semestre'], $p1)) {
            return true;
        }

        if ($periodo2 && in_array($g['semestre'], $p2)) {
            return true;
        }

        return false;
    });
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Grupos</title>
<link rel="stylesheet" href="css/grupos.css?v=2.1">

<style>
.back-arrow {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    margin: 6px;
    color: #a0522d;
    text-decoration: none;
    font-weight: bold;
    background-color: #fff5e1;
    border: 1px solid #deb887;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.back-arrow:hover {
    background-color: #deb887;
    color: #fff;
    transform: translateX(-3px);
}
.back-arrow.active {
    background-color: #a0522d;
    color: #fff;
}
</style>
</head>

<body>
<?php include_once "layout/header_admin.php"; ?>

<h1>Lista de Grupos</h1>

<a href="crearGrupo.php" class="back-arrow">Crear Grupo +</a>

<!-- 🔘 BOTONES DE PERIODO -->
<div style="margin:10px;">
    <a href="?<?= $periodo1 ? '' : 'p1=1' ?><?= $periodo2 ? '&p2=1' : '' ?>"
       class="back-arrow <?= $periodo1 ? 'active' : '' ?>"
       onclick="return confirm('¿Deseas <?= $periodo1 ? 'desactivar' : 'activar' ?> Periodo 1?');">
        Periodo 1
    </a>

    <a href="?<?= $periodo1 ? 'p1=1' : '' ?><?= $periodo2 ? '' : ($periodo1 ? '&p2=1' : 'p2=1') ?>"
       class="back-arrow <?= $periodo2 ? 'active' : '' ?>"
       onclick="return confirm('¿Deseas <?= $periodo2 ? 'desactivar' : 'activar' ?> Periodo 2?');">
        Periodo 2
    </a>
</div>

<div class="container">
<?php if (!empty($listaGrupos)): ?>
<?php foreach ($listaGrupos as $g): ?>
    <div class="card">
        <div class="headerCardGrupos">
            <a href="editar_grupo.php?idGrupo=<?= $g['idGrupo']; ?>">
                <button class="back-arrow">editar</button>
            </a>

            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('¿Estás seguro de eliminar este Grupo?');">
                <input type="hidden" name="idGrupo" value="<?= $g['idGrupo']; ?>">
                <button type="submit" name="eliminar" class="back-arrow">eliminar</button>
            </form>

            <a href="agregarAlumnosGrupo.php?idGrupo=<?= $g['idGrupo']; ?>">
                <button class="back-arrow">Ver alumnos</button>
            </a>
        </div>

        <h2><?= htmlspecialchars($g['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><strong>Semestre:</strong> <?= htmlspecialchars($g['semestre'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Tutor:</strong> <?= htmlspecialchars($g['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
        <a class="btn" href="materias.php?idGrupo=<?= $g['idGrupo']; ?>">Información del grupo</a>
    </div>
<?php endforeach; ?>
<?php else: ?>
    <p>No hay grupos registrados.</p>
<?php endif; ?>
</div>

</body>
</html>
