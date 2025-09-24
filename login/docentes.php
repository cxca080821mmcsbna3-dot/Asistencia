<?php

session_start();
if(!isset($_SESSION['idAdmin'])&& !isset($_SESSION['usuario'])){
    header('Location:index.php');
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $telefono = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];
    $correo = $_POST['correo'];

    require_once 'assets/sentenciasSQL/docentes.php';
    $docente = new Docentes();
    $alta = $docente->darAltaProfe($nombre, $apellidos, $telefono, $domicilio, $correo);
    if($alta){
        echo("<script>alert('Docente dado de alta correctamente');</script>");
    } else {
        echo("<script>alert('Error al dar de alta al docente');</script>");
    }
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
        <a href="menuGrupos.php"><button>volver</button></a>
    </header>
    <main>
        <form action="docentes.php" method="post">
        <div class="campos">
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre">
            </div>
            <div class="campo">
                <label for="apellidos">apellidos:</label>
                <input type="text" name="apellidos" id="apellidos">
            </div>
            <div class="campo">
                <label for="telefono">telefono:</label>
                <input type="text" name="telefono" id="telefono">
            </div>
            <div class="campo">
                <label for="domicilio">domicilio:</label>
                <input type="domicilio" name="domicilio" id="domicilio">
            </div>
            <div class="campo">
                <label for="correo">correo:</label>
                <input type="correo" name="correo" id="correo">
            </div>
            <input type="submit">
        </div>
        </form>
    </main>
</body>
</html>