<?php
class Materia {
    function Ingresar($nombre, $descripcion){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO materia (nombre, descripcion) 
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<body>
    <form action="materias.php" method="POST">
        <label for="nombre">Nombre de la materia:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" required>
        
        <input type="submit" value="Enviar">
    </form>
</body>
</html>
