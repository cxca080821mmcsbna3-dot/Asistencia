<?php
session_start();
if(!isset($_SESSION['idAdmin'])&& !isset($_SESSION['usuario'])){
    header('Location:index.php');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes</title>
</head>
<body>
    <header>
        <a href="menu.php"><button>volver</button></a>
    </header>
    <main>
        <div class="campos">
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre">
            </div>
        </div>
    </main>
</body>
</html>