<?php

require_once __DIR__ . "/../assets/sentenciasSQL/Conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/grupos.php";

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

$gruposObj = new Grupos();
$mensaje = "";

// Validar ID del grupo
if (!isset($_GET['idGrupo']) || !ctype_digit($_GET['idGrupo'])) {
    header("Location: grupos.php");
    exit();
}

$idGrupo = (int)$_GET['idGrupo'];

// Obtener datos actuales
$grupo = $gruposObj->leerGrupoPorId($idGrupo);
if (!$grupo) {
    $mensaje = "Grupo no encontrado.";
}

// Procesar actualización
if (isset($_POST['actualizar'])) {

    $nombre_grupo = isset($_POST['nombre_grupo']) ? trim($_POST['nombre_grupo']) : '';
    $semestre     = isset($_POST['semestre']) ? trim($_POST['semestre']) : '';
    $tutor        = isset($_POST['tutor']) ? trim($_POST['tutor']) : '';

    if (empty($nombre_grupo) || empty($semestre)) {
        $mensaje = "El nombre del grupo y el semestre son obligatorios.";
    } else {

        $nombre_grupo = htmlspecialchars($nombre_grupo, ENT_QUOTES, 'UTF-8');
        $semestre     = htmlspecialchars($semestre, ENT_QUOTES, 'UTF-8');
        $tutor        = htmlspecialchars($tutor, ENT_QUOTES, 'UTF-8');

        $actualizado = $gruposObj->actualizarGrupo(
            $idGrupo,
            $nombre_grupo,
            $semestre,
            $tutor
        );

        if ($actualizado === true) {
            $mensaje = "Grupo actualizado correctamente.";
            $grupo = $gruposObj->leerGrupoPorId($idGrupo);
        } else {
            $mensaje = "Error al actualizar el grupo. Intenta de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Editar Grupo</title>

<style>
body {
    margin: 0;
    padding: 0;
    background-color: #f9f5ef;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
}

.menu {
    background-color: #fffef9;
    max-width: 900px;
    margin: 40px auto;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    padding: 20px 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.back-arrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: #a0522d;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
}

form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

label {
    color: #a67c52;
    font-weight: 500;
}

input, textarea, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #e8d5b7;
    border-radius: 6px;
    background: #fffdfa;
    font-size: 15px;
}

button {
    background: #a67c52;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 24px;
    font-size: 16px;
    cursor: pointer;
    align-self: flex-end;
}

.message {
    width: 100%;
    padding: 10px;
    text-align: center;
    border-radius: 6px;
}

.success {
    background: #fffdfa;
    border: 1px solid #e8d5b7;
}

.error {
    background: #fbd1d1;
    border: 1px solid #f8b0b0;
}
</style>
</head>

<body>

<div class="menu">
<a href="gruposCreados.php" class="back-arrow">&#8592; Regresar</a>

<?php if ($mensaje): ?>
<p class="message <?= (strpos($mensaje,'correctamente')!==false)?'success':'error' ?>">
<?= htmlspecialchars($mensaje) ?>
</p>
<?php endif; ?>

<?php if ($grupo): ?>
<form method="POST">

<label>Nombre del Grupo</label>
<input type="text" name="nombre_grupo" value="<?= htmlspecialchars($grupo['nombre']) ?>" required>

<label>Semestre</label>
<select name="semestre" required>
<option value="">Selecciona un semestre</option>
<?php
$semestres = [
    'Primer semestre',
    'Segundo semestre',
    'Tercer semestre',
    'Cuarto semestre',
    'Quinto semestre',
    'Sexto semestre'
];
foreach ($semestres as $s):
?>
<option value="<?= $s ?>" <?= ($grupo['semestre']===$s)?'selected':'' ?>><?= $s ?></option>
<?php endforeach; ?>
</select>

<label>Tutor</label>
<textarea name="tutor" rows="2"><?= htmlspecialchars($grupo['tutor']) ?></textarea>

<button type="submit" name="actualizar">Actualizar Grupo</button>

</form>
<?php endif; ?>
</div>

</body>
</html>
