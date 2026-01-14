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
    // Validar que los campos no estén vacíos
    $nombre_grupo = isset($_POST['nombre_grupo']) ? trim($_POST['nombre_grupo']) : '';
    $descripcion  = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $tutor        = isset($_POST['tutor']) ? trim($_POST['tutor']) : '';

    if (empty($nombre_grupo)) {
        $mensaje = "El nombre del grupo es obligatorio.";
    } else {
        // Sanitizar entradas
        $nombre_grupo = htmlspecialchars($nombre_grupo, ENT_QUOTES, 'UTF-8');
        $descripcion  = htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8');
        $tutor        = htmlspecialchars($tutor, ENT_QUOTES, 'UTF-8');

        $actualizado = $gruposObj->actualizarGrupo($idGrupo, $nombre_grupo, $descripcion, $tutor);

        if ($actualizado === true) {
            $mensaje = "Grupo actualizado correctamente.";
            // refrescar datos
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
    max-width: 500px;
    margin: 40px auto;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    padding: 20px 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.regresar {
    background: #a67c52;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 18px;
    font-size: 16px;
    cursor: pointer;
    margin-bottom: 18px;
    transition: background 0.2s;
    align-self: flex-start;
}

.regresar:hover {
    background: #deb887;
}

form#formulario_grupo {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

label {
    color: #a67c52;
    font-weight: 500;
    margin-bottom: 6px;
}

input[type="text"], textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e8d5b7;
    border-radius: 6px;
    background: #fffdfa;
    font-size: 15px;
    box-sizing: border-box;
    transition: border 0.2s;
}

input[type="text"]:focus, textarea:focus {
    border-color: #a67c52;
    outline: none;
}

button[type="submit"] {
    background: #a67c52;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 24px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 8px;
    transition: background 0.2s;
    align-self: flex-end;
}

button[type="submit"]:hover {
    background: #deb887;
}

.message {
    margin: 12px 0 0 0;
    padding: 10px;
    border-radius: 6px;
    font-size: 15px;
    width: 100%;
    text-align: center;
}

.message.success {
    background: #fffdfa;
    color: #a67c52;
    border: 1px solid #e8d5b7;
}

.message.error {
    background: #fbd1d1;
    color: #c62828;
    border: 1px solid #f8b0b0;
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
    transition: color 0.3s ease, transform 0.2s ease;
}

.back-arrow:hover {
    color: #deb887;
    transform: translateX(-3px);
}

        </style>
</head>

<body>

<div class="menu">
    <a href="gruposCreados.php" class="back-arrow">&#8592; Regresar</a>

    <?php if ($mensaje): ?>
        <p class="message <?= (strpos($mensaje, 'correctamente') !== false) ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($grupo): ?>
        <form id="formulario_grupo" action="" method="POST" novalidate>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label for="nombre_grupo">Nombre del Grupo:</label>
                    <input type="text" id="nombre_grupo" name="nombre_grupo" maxlength="100"
                           value="<?= htmlspecialchars($grupo['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div>
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"><?= htmlspecialchars($grupo['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div>
                    <label for="tutor">Tutor:</label>
                    <textarea id="tutor" name="tutor" rows="2" maxlength="200"><?= htmlspecialchars($grupo['tutor'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <button type="submit" name="actualizar">Actualizar Grupo</button>
            </div>
        </form>
    <?php else: ?>
        <p>Grupo no encontrado.</p>
    <?php endif; ?>
</div>

</body>
</html>
