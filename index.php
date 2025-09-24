<?php
//ya esta este archivo inicial terminado
session_start();
require_once __DIR__. "../assets/sentenciasSQL/admin.php";
$error = ""; 

if (isset($_POST['iniciar'])&& !empty($_POST['usuario']) && !empty($_POST['contrasena'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    $admin = new Admin();
    $adminData = $admin->leerAdmin($usuario, $contrasena);
        if ($adminData) {
            // esto crea la sesión
            echo "sesion iniciada";
            $_SESSION['idAdmin'] = $adminData['idAdmin'];
            $_SESSION['usuario'] = $adminData['usuario'];
            header("Location: menuGrupos.php"); 
            exit();
        } elseif($adminData === false) {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Login</h1>
            <?php if (!empty($error)): ?>
                <p style="color:cornflowerblue;; text-align:center;"><?= $error ?></p>
            <?php endif; ?>

            <div class="input-box">
                <label for="usuario" class="tex">Usuario:</label><br>
                <input type="text" name="usuario" id="usuario" class="TEXTO" required>
            </div>
            <br>
            <div class="input-box">
                <label for="contrasena" class="tex">Contraseña:</label><br>
                <input type="password" name="contrasena" id="contrasena" class="TEXTO" required>
            </div>
            <br>
            <div class="button-group">
                <button type="submit" name="iniciar" class="btn">Iniciar sesión</button>
            </div>
        </form>
    </div>
</body>
</html>
