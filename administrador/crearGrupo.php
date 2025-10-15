<?php

include_once(__DIR__ . '/../assets/sentenciasSQL/grupos.php');

if (isset($_POST['crear'])) {
    $idGrupo        = random_int(10000000, 99999999);
    $nombre_grupo  = htmlspecialchars(trim($_POST['nombre_grupo']), ENT_QUOTES, 'UTF-8');
    $descripcion    = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $tutor          = htmlspecialchars(trim($_POST['tutor']), ENT_QUOTES, 'UTF-8');
    


    $crear_grupos = new Grupos();
    $crear = $crear_grupos->crearGrupo($idGrupo, $nombre_grupo, $descripcion, $tutor);

    if ($crear === true) {
        echo "<script>alert('Grupo creado exitosamente'); window.location='crearGrupo.php';</script>";
        exit();
    } elseif ($crear === 'duplicado') {
        echo "<script>alert('Grupo ya existente. Intenta de nuevo.');</script>";
    } else {
        echo "<script>alert('Error al crear el grupo. Por favor, intenta de nuevo.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Evento</title>

</head>
<style>
input.valid, textarea.valid {
  border: 2px solid green;
  background: #e8f5e9;
}
input.invalid, textarea.invalid {
  border: 2px solid red;
  background: #ffebee;
}
</style>
<body>
        <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>


    <h2>Agregar Grupo</h2>
   <form id="formGrupo" action="crearGrupo.php" method="POST" enctype="multipart/form-data">
    <label>Nombre del Grupo:</label>
    <input type="text" id="nombre_grupo" name="nombre_grupo" required>

    <label>Descripción:</label>
    <textarea id="descripcion" name="descripcion"></textarea>

    <label>Tutor:</label>
    <textarea id="tutor" name="tutor"></textarea>

    <br><br>
    <button type="submit" name="crear">Agregar Grupo</button>
</form>

</body>
</html>
