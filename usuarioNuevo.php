<?php
class Materia {
    function Ingresar($usuario, $contrasena){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO maestro (usuario, contrasena) 
                                   VALUES(:usuario, :contrasena)");

            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':contrasena', $contrasena);

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
    if (isset($_POST['usuario']) && isset($_POST['contrasena'])) {
        $materia = new Materia();
        $materia->Ingresar($_POST['usuario'], $_POST['contrasena']);
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
        <label for="usuario">Nombre de la materia:</label>
        <input type="text" name="usuario" id="usuario" required>
        
        <label for="contrasena">Descripción:</label>
        <input type="text" name="contrasena" id="contrasena" required>
        
        <input type="submit" value="Enviar">
    </form>
</body>
</html>
