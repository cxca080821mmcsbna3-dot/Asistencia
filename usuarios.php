<?php
include_once __DIR__ . "/assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/assets/sentenciasSQL/grupos.php";

$gruposObj = new Grupos();
$listaGrupos = $gruposObj->leerGrupos();

$mensaje = ''; // <- Aquí se guarda el mensaje a mostrar

if (isset($_POST['crear'])) {
    $tipo = $_POST['tipo_usuario'];

    if ($tipo === 'profesor') {
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $telefono = $_POST['telefono'];
        $domicilio = $_POST['domicilio'];
        $correo = $_POST['correo'];

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM profesor WHERE correo = ? OR telefono = ?");
        $stmt->execute([$correo, $telefono]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Error: El correo o teléfono ya está registrado para un profesor.";
        } else {
            $sql = "INSERT INTO profesor (nombre, apellidos, telefono, domicilio, correo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo]);
            $mensaje = "Profesor creado correctamente.";
        }

    } elseif ($tipo === 'alumno') {
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $domicilio = $_POST['domicilio'];
        $id_grupo = $_POST['id_grupo'] ?? null;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupo WHERE idGrupo = ?");
        $stmt->execute([$id_grupo]);
        if ($stmt->fetchColumn() == 0) {
            $mensaje = "Error: El grupo seleccionado no existe.";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM alumno WHERE correo = ? OR telefono = ?");
            $stmt->execute([$correo, $telefono]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "Error: El correo o teléfono ya está registrado para un alumno.";
            } else {
                $sql = "INSERT INTO alumno (nombre, apellidos, correo, telefono, domicilio, id_grupo) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $apellidos, $correo, $telefono, $domicilio, $id_grupo]);
                $mensaje = "Alumno creado correctamente.";
            }
        }

    } elseif ($tipo === 'administrador') {
        $usuario = $_POST['usuario'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrador WHERE usuario = ?");
        $stmt->execute([$usuario]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Error: Usuario no disponible, ya existe.";
        } else {
            $sql = "INSERT INTO administrador (usuario, password) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario, $password]);
            $mensaje = "Administrador creado correctamente.";
        }
    }
}

$tipo = $_POST['tipo_usuario'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="assets/css/usuarios.css">
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
</head>
<body>
<div class="usuarios">
    <h2>Crear Usuario</h2>

    <!-- Aquí mostramos el mensaje -->
    <?php if (!empty($mensaje)): ?>
        <div style="background-color: #a4b1eeff; color: #000000ff; border: 1px solid #000000ff; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Tipo de Usuario:</label><br>
        <select name="tipo_usuario" onchange="this.form.submit()" required>
            <option value="">Selecciona un tipo</option>
            <option value="profesor" <?= ($tipo == 'profesor') ? 'selected' : '' ?>>Profesor</option>
            <option value="alumno" <?= ($tipo == 'alumno') ? 'selected' : '' ?>>Alumno</option>
            <option value="administrador" <?= ($tipo == 'administrador') ? 'selected' : '' ?>>Administrador</option>
        </select><br><br>

        <?php if ($tipo == 'profesor' || $tipo == 'alumno'): ?>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" required><br><br>

            <label>Apellidos:</label><br>
            <input type="text" name="apellidos" required><br><br>

            <label>Correo:</label><br>
            <input type="email" name="correo" required><br><br>

            <label>Teléfono:</label><br>
            <input type="text" name="telefono" required><br><br>

            <label>Domicilio:</label><br>
            <input type="text" name="domicilio" required><br><br>

            <?php if ($tipo == 'alumno'): ?>
                <label>Grupo:</label><br>
                <select name="id_grupo" required>
                    <option value="">Selecciona un grupo</option>
                    <?php foreach ($listaGrupos as $grupo): ?>
                        <option value="<?= $grupo['idGrupo'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
                    <?php endforeach; ?>
                </select><br><br>
            <?php endif; ?>

        <?php elseif ($tipo == 'administrador'): ?>

            <label>Usuario:</label><br>
            <input type="text" name="usuario" required><br><br>

            <label>Contraseña:</label><br>
            <input type="password" name="password" required><br><br>

        <?php else: ?>
            <p>Por favor, selecciona un tipo de usuario para mostrar los campos correspondientes.</p>
        <?php endif; ?>

        <?php if ($tipo != ''): ?>
            <button type="submit" name="crear">Crear Usuario</button>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
