<?php

require_once __DIR__ . "/assets/sentenciasSQL/Conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/grupos.php";

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
        input.valid, textarea.valid {
            border: 2px solid green;
            background: #e8f5e9;
        }

        input.invalid, textarea.invalid {
            border: 2px solid red;
            background: #ffebee;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            font-size: 1rem;
        }

        button.regresar {
            margin-bottom: 20px;
            padding: 8px 15px;
            cursor: pointer;
        }

        button[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <button class="regresar" onclick="window.history.back()">Volver</button>
    <h2>Editar Grupo</h2>

    <?php if ($mensaje): ?>
        <p style="color:<?= (strpos($mensaje, 'correctamente') !== false) ? 'green' : 'red'; ?>;">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($grupo): ?>
        <form id="formGrupo" action="" method="POST" novalidate>
            <label for="nombre_grupo">Nombre del Grupo:</label>
            <input type="text" id="nombre_grupo" name="nombre_grupo" maxlength="100"
                   value="<?= htmlspecialchars($grupo['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"><?= htmlspecialchars($grupo['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="tutor">Tutor:</label>
            <textarea id="tutor" name="tutor" rows="2" maxlength="200"><?= htmlspecialchars($grupo['tutor'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <button type="submit" name="actualizar">Actualizar Grupo</button>
        </form>
    <?php else: ?>
        <p>Grupo no encontrado.</p>
    <?php endif; ?>
</body>
</html>
