<?php

include_once __DIR__ . ("../../assets/sentenciasSQL/grupos.php");

if (isset($_POST['crear'])) {
    $idMateria        = random_int(10000000, 99999999);
    $nombre_materia  = htmlspecialchars(trim($_POST['nombre_materia']), ENT_QUOTES, 'UTF-8');
    $profesor          = htmlspecialchars(trim($_POST['profesor']), ENT_QUOTES, 'UTF-8');
    


    $crear_materias = new Materias();
    $crear = $crear_materias->crearMateria($idMateria, $nombre_materia, $profesor);

    if ($crear === true) {
        echo "<script>alert('Materia creada con exitosamente'); window.location='crearMaterias.php';</script>";
        exit();
    } elseif ($crear === 'duplicado') {
        echo "<script>alert('Esta materia ya existe. Intenta de nuevo.');</script>";
    } else {
        echo "<script>alert('Error al crear una meteria. Por favor, intenta de nuevo.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Materias</title>

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

    <h2>Agregar Materia</h2>
   <form id="formGrupo" action="crearGrupo.php" method="POST" enctype="multipart/form-data">
    <label>Nombre de la materia:</label>
    <input type="text" id="nombre_materia" name="nombre_materia" required>

    <label>Profesor:</label>
    <textarea id="profesor" name="profesor"></textarea>

    <br><br>
    <button type="submit" name="crear">Agregar Materia</button>
</form>

</body>
</html>
