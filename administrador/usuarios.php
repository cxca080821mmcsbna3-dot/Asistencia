<?php
include_once(__DIR__ . '/../assets/sentenciasSQL/conexion.php');
require_once(__DIR__ . '/../assets/sentenciasSQL/grupos.php');

$gruposObj = new Grupos();
$listaGrupos = $gruposObj->leerGrupos();

$mensaje = '';
$tipo = $_POST['tipo_usuario'] ?? $_GET['tipo'] ?? '';
$editarID = $_GET['editar'] ?? null;

// 🧼 BORRAR
if (isset($_GET['eliminar'], $_GET['tipo'])) {
    $id = $_GET['eliminar'];
    $tipoEliminar = $_GET['tipo'];

    switch ($tipoEliminar) {
        case 'profesor':
            $stmt = $pdo->prepare("DELETE FROM profesor WHERE id_profesor = ?");
            break;
        case 'alumno':
            $stmt = $pdo->prepare("DELETE FROM alumno WHERE id_alumno = ?");
            break;
        case 'administrador':
            $stmt = $pdo->prepare("DELETE FROM administrador WHERE id_admin = ?");
            break;
    }
    $stmt->execute([$id]);
    header("Location: usuarios.php?tipo=$tipo&mensaje=Eliminado correctamente");
    exit;
}

// ✅ CREAR o ACTUALIZAR
if (isset($_POST['crear'])) {
    $tipo = $_POST['tipo_usuario'];

    if ($tipo === 'profesor') {
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $telefono = $_POST['telefono'];
        $domicilio = $_POST['domicilio'];
        $correo = $_POST['correo'];

        if (isset($_POST['id_profesor']) && $_POST['id_profesor'] != '') {
            $stmt = $pdo->prepare("UPDATE profesor SET nombre=?, apellidos=?, telefono=?, domicilio=?, correo=? WHERE id_profesor=?");
            $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo, $_POST['id_profesor']]);
            $mensaje = "Profesor actualizado correctamente.";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM profesor WHERE correo = ? OR telefono = ?");
            $stmt->execute([$correo, $telefono]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "Error: El correo o teléfono ya está registrado para un profesor.";
            } else {
                $sql = "INSERT INTO profesor (nombre, apellidos, telefono, domicilio, correo, password) VALUES (?, ?, ?, ?, ?, '')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo]);
                $mensaje = "Profesor creado correctamente.";
            }
        }

        } elseif ($tipo === 'alumno') {
        $matricula = $_POST['matricula'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $telefono = $_POST['telefono'];

        if (isset($_POST['id_alumno']) && $_POST['id_alumno'] != '') {
            // Actualizar alumno (sin grupo)
            $stmt = $pdo->prepare("UPDATE alumno SET matricula=?, nombre=?, apellidos=?, telefono=? WHERE id_alumno=?");
            $stmt->execute([$matricula, $nombre, $apellidos, $telefono, $_POST['id_alumno']]);
            $mensaje = "Alumno actualizado correctamente.";
        } else {
            // Verificar si la matrícula ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM alumno WHERE matricula = ?");
            $stmt->execute([$matricula]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "Error: La matrícula ya está registrada.";
            } else {
                // Crear nuevo alumno (sin grupo asignado)
                $sql = "INSERT INTO alumno (matricula, nombre, apellidos, telefono) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$matricula, $nombre, $apellidos, $telefono]);
                $mensaje = "Alumno creado correctamente.";
            }
        }

    } elseif ($tipo === 'administrador') {
        $usuario = $_POST['usuario'];

        if (isset($_POST['id_admin']) && $_POST['id_admin'] != '') {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE administrador SET usuario=?, password=? WHERE id_admin=?");
                $stmt->execute([$usuario, $password, $_POST['id_admin']]);
            } else {
                $stmt = $pdo->prepare("UPDATE administrador SET usuario=? WHERE id_admin=?");
                $stmt->execute([$usuario, $_POST['id_admin']]);
            }
            $mensaje = "Administrador actualizado correctamente.";
        } else {
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
}

// 🔍 Obtener datos para editar
$datosEditar = null;
if ($editarID && $tipo) {
    switch ($tipo) {
        case 'profesor':
            $stmt = $pdo->prepare("SELECT * FROM profesor WHERE id_profesor = ?");
            break;
        case 'alumno':
            $stmt = $pdo->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
            break;
        case 'administrador':
            $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id_admin = ?");
            break;
    }
    $stmt->execute([$editarID]);
    $datosEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/usuarios.css?v=2.1">
    <meta charset="UTF-8">
    <title>Usuarios</title>
</head>
<body>
<div class="usuarios">
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

    <h2><?= $editarID ? 'Editar Usuario' : 'Crear Usuario' ?></h2>

    <?php if (!empty($mensaje)): ?>
        <div style="background-color: #a4b1eeff; color: #000000ff; border: 1px solid #000000ff; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Tipo de Usuario:</label><br>
        <select name="tipo_usuario" onchange="this.form.submit()" required>
            <option value="">Selecciona un tipo</option>
            <option value="profesor" <?= ($tipo == 'profesor') ? 'selected' : '' ?>>Profesor</option>
            <option value="alumno" <?= ($tipo == 'alumno') ? 'selected' : '' ?>>Alumno</option>
            <option value="administrador" <?= ($tipo == 'administrador') ? 'selected' : '' ?>>Administrador</option>
        </select><br><br>

        <?php if ($tipo == 'profesor'): ?>
            <input type="text" name="nombre" placeholder="Nombre" value="<?= $datosEditar['nombre'] ?? '' ?>" required><br><br>
            <input type="text" name="apellidos" placeholder="Apellidos" value="<?= $datosEditar['apellidos'] ?? '' ?>" required><br><br>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= $datosEditar['telefono'] ?? '' ?>" required><br><br>
            <input type="text" name="domicilio" placeholder="Domicilio" value="<?= $datosEditar['domicilio'] ?? '' ?>" required><br><br>
            <input type="email" name="correo" placeholder="Correo" value="<?= $datosEditar['correo'] ?? '' ?>" required><br><br>
            <input type="hidden" name="id_profesor" value="<?= $datosEditar['id_profesor'] ?? '' ?>">

        <?php elseif ($tipo == 'alumno'): ?>
            <input type="number" name="matricula" placeholder="Matrícula" value="<?= $datosEditar['matricula'] ?? '' ?>" required><br><br>
            <input type="text" name="nombre" placeholder="Nombre" value="<?= $datosEditar['nombre'] ?? '' ?>" required><br><br>
            <input type="text" name="apellidos" placeholder="Apellidos" value="<?= $datosEditar['apellidos'] ?? '' ?>" required><br><br>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= $datosEditar['telefono'] ?? '' ?>" required><br><br>
            <input type="hidden" name="id_alumno" value="<?= $datosEditar['id_alumno'] ?? '' ?>">

        <?php elseif ($tipo == 'administrador'): ?>
            <input type="text" name="usuario" placeholder="Usuario" value="<?= $datosEditar['usuario'] ?? '' ?>" required><br><br>
            <input type="password" name="password" placeholder="<?= $editarID ? 'Nueva Contraseña (opcional)' : 'Contraseña' ?>" <?= $editarID ? '' : 'required' ?>><br><br>
            <input type="hidden" name="id_admin" value="<?= $datosEditar['id_admin'] ?? '' ?>">
        <?php endif; ?>

        <?php if ($tipo != ''): ?>
            <button type="submit" name="crear"><?= $editarID ? 'Actualizar' : 'Crear' ?></button>
        <?php endif; ?>
    </form>
</div>

<hr><h3>Listado de <?= ucfirst($tipo) ?>s</h3>

<?php
if ($tipo) {
    switch ($tipo) {
        case 'profesor':
            $stmt = $pdo->query("SELECT * FROM profesor");
            break;
        case 'alumno':
            $stmt = $pdo->query("SELECT * FROM alumno");
            break;
        case 'administrador':
            $stmt = $pdo->query("SELECT * FROM administrador");
            break;
    }
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($usuarios) {
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr>';
        foreach (array_keys($usuarios[0]) as $campo) {
            echo "<th>" . htmlspecialchars($campo) . "</th>";
        }
        echo '<th>Acciones</th></tr>';

        foreach ($usuarios as $usuario) {
            echo '<tr>';
            foreach ($usuario as $valor) {
                echo "<td>" . htmlspecialchars($valor) . "</td>";
            }
            $idCampo = array_keys($usuario)[0];
            echo "<td>
                <a href='?editar=" . $usuario[$idCampo] . "&tipo=$tipo'>Editar</a> |
                <a href='?eliminar=" . $usuario[$idCampo] . "&tipo=$tipo' onclick='return confirm(\"¿Seguro que deseas eliminar este usuario?\")'>Eliminar</a>
            </td>";
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "<p>No hay registros de este tipo.</p>";
    }
}
?>
</body>
</html>
