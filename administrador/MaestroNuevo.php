<?php
class Maestros {
    function Ingresar($maestro, $contrasena){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO maestro (maestro, contrasena) 
                                   VALUES(:maestro, :contrasena)");

            $stmt->bindParam(':maestro', $maestro);
            $stmt->bindParam(':contrasena', $contrasena);

            if($stmt->execute()) {
                echo "maestro ingresada correctamente.";
            } else {
                echo "Error al ingresar la maestro.";
            }
        } else {
            echo "Error de conexión a la base de datos.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['maestro']) && isset($_POST['contrasena'])) {
        $maestro = new Maestros();
        $maestro->Ingresar($_POST['maestro'], $_POST['contrasena']);
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
    <title>Registrar Maestros</title>
</head>
<body>
    <form action="MaestroNuevo.php" method="POST">
        <label for="maestro">Nombre del maestro:</label>
        <input type="text" name="maestro" id="maestro" required>
        <br>
        <label for="contrasena">Contraseña:</label>
        <input type="text" name="contrasena" id="contrasena" required>
        
        <input type="submit" value="Enviar">
    </form>
</body>
</html>
