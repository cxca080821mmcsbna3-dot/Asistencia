<?php
session_start();
require_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/claseUsuarios.php";

$error = "";

if (isset($_POST['iniciar'])) {
    $usuario = trim($_POST['usuario']); // Puede ser correo o matrícula
    $contrasena = trim($_POST['contrasena']); // Contraseña o CURP

    if (empty($usuario) || empty($contrasena)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        try {
            // ======= ADMIN =======
            $admin = new Admin($pdo);
            $adminData = $admin->leerAdmin($usuario, $contrasena);
            if ($adminData) {
                $_SESSION['rol'] = 'admin';
                $_SESSION['idAdmin'] = $adminData['id_admin'];
                $_SESSION['nombre'] = $adminData['nombre'];
                header("Location: administrador/menuGrupos.php");
                exit();
            }

            // ======= PROFESOR =======
            $profesor = new Profesor($pdo);
            $profesorData = $profesor->leerProfesor($usuario, $contrasena);
            if ($profesorData) {
                $_SESSION['rol'] = 'profesor';
                $_SESSION['idProfesor'] = $profesorData['id_profesor'];
                $_SESSION['correo'] = $profesorData['correo'];
                $_SESSION['nombre'] = $profesorData['nombre'];
                header("Location: Docentes/menuDocente.php");
                exit();
            }

            // ======= ALUMNO =======
            $alumno = new Alumno($pdo);
            $alumnoData = $alumno->buscarPorMatriculaYCurp($usuario, $contrasena);
            if ($alumnoData) {
                $_SESSION['rol'] = 'alumno';
                $_SESSION['idAlumno'] = $alumnoData['id_alumno'];
                $_SESSION['nombre'] = $alumnoData['nombre'];
                $_SESSION['matricula'] = $alumnoData['matricula'];
                $_SESSION['apellidos'] = $alumnoData['apellidos'];
                header("Location: alumno/menu_alumno.php");
                exit();
            }

            // Ninguno coincidió
            $error = "❌ Credenciales incorrectas o usuario no registrado.";
        } catch (PDOException $e) {
            // Evitar mostrar detalles técnicos al usuario; registrar el error para diagnósticos
            error_log("[Login] Error de base de datos: " . $e->getMessage());
            $error = "❌ Error al comunicarse con la base de datos. Intenta más tarde.";
        } catch (Exception $e) {
            error_log("[Login] Error inesperado: " . $e->getMessage());
            $error = "❌ Ocurrió un error. Intenta de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=123">
    <style>
        .login-alert {
            background: #ffe6e6;
            color: #b30000;
            border: 1px solid #ffb3b3;
            border-radius: 8px;
            padding: 12px 18px;
            margin: 0 0 18px 0;
            display: flex;
            align-items: center;
            font-size: 1.1em;
            box-shadow: 0 2px 8px 0 #f8d7da44;
            justify-content: center;
            gap: 10px;
            animation: fadeIn 0.7s;
        }
        .login-alert-icon {
            font-size: 1.5em;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Login</h1>

            <?php if (!empty($error)): ?>
                <div class="login-alert">
                    <span class="login-alert-icon">&#9888;</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="input-box">
                <label for="usuario" class="tex">Correo o Matrícula:</label><br>
                <input type="text" name="usuario" id="usuario" class="TEXTO" required>
            </div>
            <br>
            <div class="input-box">
                <label for="contrasena" class="tex">Contraseña o CURP:</label><br>
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


