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
        $correo = trim($_POST['correo']);
        $password = $_POST['password'] ?? '';

        if (isset($_POST['id_profesor']) && $_POST['id_profesor'] != '') {
            // Actualizar profesor
            if (!empty($password)) {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE profesor SET nombre=?, apellidos=?, telefono=?, domicilio=?, correo=?, password=? WHERE id_profesor=?");
                $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo, $passHash, $_POST['id_profesor']]);
            } else {
                $stmt = $pdo->prepare("UPDATE profesor SET nombre=?, apellidos=?, telefono=?, domicilio=?, correo=? WHERE id_profesor=?");
                $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo, $_POST['id_profesor']]);
            }
            $mensaje = "Profesor actualizado correctamente.";
        } else {
$stmt = $pdo->prepare("SELECT COUNT(*) FROM profesor WHERE correo = ?");
$stmt->execute([$correo]);

if ($stmt->fetchColumn() > 0) {
    $mensaje = "Error: El correo ya está registrado para un profesor.";
} elseif (empty(trim($password))) {
    $mensaje = "Error: Debes escribir una contraseña";
} else {
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO profesor (nombre, apellidos, telefono, domicilio, correo, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $apellidos, $telefono, $domicilio, $correo, $passHash]);
    $mensaje = "Profesor creado correctamente.";
}
        }

    } elseif ($tipo === 'alumno') {
        $matricula = $_POST['matricula'];
        $curp = trim($_POST['curp']);
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $telefono = $_POST['telefono'];

        if (isset($_POST['id_alumno']) && $_POST['id_alumno'] != '') {
            $stmt = $pdo->prepare("UPDATE alumno SET matricula=?, curp=?, nombre=?, apellidos=?, telefono=? WHERE id_alumno=?");
            $stmt->execute([$matricula, $curp, $nombre, $apellidos, $telefono, $_POST['id_alumno']]);
            $mensaje = "Alumno actualizado correctamente.";
        } else {
            // Verificar si la matrícula ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM alumno WHERE matricula = ?");
            $stmt->execute([$matricula]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "Error: La matrícula ya está registrada.";
            } else {
                $sql = "INSERT INTO alumno (matricula, curp, nombre, apellidos, telefono) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$matricula, $curp, $nombre, $apellidos, $telefono]);
                $mensaje = "Alumno creado correctamente.";
            }
        }

    } elseif ($tipo === 'administrador') {
        $nombre = $_POST['nombre'];
        $correo = trim($_POST['correo']);
        $password = $_POST['password'] ?? '';

        if (isset($_POST['id_admin']) && $_POST['id_admin'] != '') {
            if (!empty($password)) {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE administrador SET nombre=?, correo=?, password=? WHERE id_admin=?");
                $stmt->execute([$nombre, $correo, $passHash, $_POST['id_admin']]);
            } else {
                $stmt = $pdo->prepare("UPDATE administrador SET nombre=?, correo=? WHERE id_admin=?");
                $stmt->execute([$nombre, $correo, $_POST['id_admin']]);
            }
            $mensaje = "Administrador actualizado correctamente.";
        } else {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrador WHERE correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "Error: El correo ya está registrado para un administrador.";
            } else {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO administrador (nombre, correo, password) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $correo, $passHash]);
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
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="css/usuarios.css?v=2.1">

</head>
<body>
<div class="usuarios">
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

    <h2><?= $editarID ? 'Editar Usuario' : 'Crear Usuario' ?></h2>

    <?php if (!empty($mensaje)): ?>
        <div >
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Tipo de Usuario:</label>
        <select name="tipo_usuario" onchange="this.form.submit()" required>
            <option value="">Selecciona un tipo</option>
            <option value="profesor" <?= ($tipo == 'profesor') ? 'selected' : '' ?>>Profesor</option>
            <option value="alumno" <?= ($tipo == 'alumno') ? 'selected' : '' ?>>Alumno</option>
            <option value="administrador" <?= ($tipo == 'administrador') ? 'selected' : '' ?>>Administrador</option>
        </select>

        <?php if ($tipo == 'profesor'): ?>
            <input type="text" name="nombre" placeholder="Nombre" value="<?= $datosEditar['nombre'] ?? '' ?>" required>
            <input type="text" name="apellidos" placeholder="Apellidos" value="<?= $datosEditar['apellidos'] ?? '' ?>" required>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= $datosEditar['telefono'] ?? '' ?>" required>
            <input type="text" name="domicilio" placeholder="Domicilio" value="<?= $datosEditar['domicilio'] ?? '' ?>" required>
            <input type="email" name="correo" placeholder="Correo" value="<?= $datosEditar['correo'] ?? '' ?>" required>
            <input type="password" name="password" placeholder="<?= $editarID ? 'Nueva Contraseña (opcional)' : 'Contraseña' ?>" <?= $editarID ? '' : 'required' ?>>
            <input type="hidden" name="id_profesor" value="<?= $datosEditar['id_profesor'] ?? '' ?>">

        <?php elseif ($tipo == 'alumno'): ?>
            <input type="number" name="matricula" placeholder="Matrícula" value="<?= $datosEditar['matricula'] ?? '' ?>" required>
            <input type="text" name="curp" placeholder="CURP" value="<?= $datosEditar['curp'] ?? '' ?>" required>
            <input type="text" name="nombre" placeholder="Nombre" value="<?= $datosEditar['nombre'] ?? '' ?>" required>
            <input type="text" name="apellidos" placeholder="Apellidos" value="<?= $datosEditar['apellidos'] ?? '' ?>" required>
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= $datosEditar['telefono'] ?? '' ?>" required>
            <input type="hidden" name="id_alumno" value="<?= $datosEditar['id_alumno'] ?? '' ?>">

        <?php elseif ($tipo == 'administrador'): ?>
            <input type="text" name="nombre" placeholder="Nombre" value="<?= $datosEditar['nombre'] ?? '' ?>" required>
            <input type="email" name="correo" placeholder="Correo" value="<?= $datosEditar['correo'] ?? '' ?>" required>
            <input type="password" name="password" placeholder="<?= $editarID ? 'Nueva Contraseña (opcional)' : 'Contraseña' ?>" <?= $editarID ? '' : 'required' ?>>
            <input type="hidden" name="id_admin" value="<?= $datosEditar['id_admin'] ?? '' ?>">
        <?php endif; ?>

        <?php if ($tipo != ''): ?>
            <button type="submit" name="crear"><?= $editarID ? 'Actualizar' : 'Crear' ?></button>
        <?php endif; ?>
    </form>
</div>

<?php if ($tipo): ?>
    <hr>
    <h3>Listado de <?= ucfirst($tipo) ?>s</h3>
    <?php
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

    if ($usuarios):
    ?>
        <table>
            <tr>
                <?php foreach(array_keys($usuarios[0]) as $campo): ?>
                    <th><?= htmlspecialchars($campo) ?></th>
                <?php endforeach; ?>
                <th>Acciones</th>
            </tr>
            <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <?php foreach($usuario as $valor): ?>
                        <td><?= htmlspecialchars($valor) ?></td>
                    <?php endforeach; ?>
                    <?php $idCampo = array_keys($usuario)[0]; ?>
                    <td>
                        <a href="?editar=<?= $usuario[$idCampo] ?>&tipo=<?= $tipo ?>">Editar</a> |
                        <a href="?eliminar=<?= $usuario[$idCampo] ?>&tipo=<?= $tipo ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No hay registros de este tipo.</p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
