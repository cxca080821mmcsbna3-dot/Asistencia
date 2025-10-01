<?php
class Materia {
    function Ingresar($nombre, $descripcion){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO materias (nombre, descripcion) 
                                   VALUES(:nombre, :descripcion)");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);

            if($stmt->execute()) {
                echo "Materia ingresada correctamente.";
            } else {
                echo "Error al ingresar la materia.";
            }
        } else {
            echo "Error de conexión a la base de datos.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['descripcion'])) {
        $materia = new Materia();
        $materia->Ingresar($_POST['nombre'], $_POST['descripcion']);
    } else {
        echo "Faltan datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="assets/css/materias.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<body>
    <div class="materias">
    <form action="materias.php" method="POST">
        <h1></h1>
        <label for="nombre">Nombre de la materia:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>
        
        <label for="descripcion">Descripción:</label><br>
        <input type="text" name="descripcion" id="descripcion" required><br><br>
        <input type="submit" value="Enviar">
    </form>
    <div
</body>
</html>
