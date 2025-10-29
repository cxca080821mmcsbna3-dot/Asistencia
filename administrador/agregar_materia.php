<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');

class Materia {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function Ingresar($nombre, $descripcion) {
        $stmt = $this->pdo->prepare("INSERT INTO materias (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    public function Consultar() {
        $stmt = $this->pdo->query("SELECT * FROM materias ORDER BY id_materia DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ObtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM materias WHERE id_materia = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function Actualizar($id, $nombre, $descripcion) {
        $stmt = $this->pdo->prepare("UPDATE materias SET nombre = :nombre, descripcion = :descripcion WHERE id_materia = :id");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function Eliminar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM materias WHERE id_materia = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

$materiaObj = new Materia($pdo);
$mensaje = "";
$materiaEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['accion'] === 'agregar') {
        $materiaObj->Ingresar($_POST['nombre'], $_POST['descripcion']);
        $mensaje = "✅ Materia agregada correctamente.";
    } elseif ($_POST['accion'] === 'actualizar') {
        $materiaObj->Actualizar($_POST['id_materia'], $_POST['nombre'], $_POST['descripcion']);
        $mensaje = "✅ Materia actualizada correctamente.";
    }
}

if (isset($_GET['eliminar'])) {
    $materiaObj->Eliminar($_GET['eliminar']);
    $mensaje = "🗑️ Materia eliminada correctamente.";
}

if (isset($_GET['editar'])) {
    $materiaEditar = $materiaObj->ObtenerPorId($_GET['editar']);
}

$listaMaterias = $materiaObj->Consultar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias</title>
    <link rel="stylesheet" href="css/materiascrud.css">
</head>
<body>

<a href="menuGrupos.php" class="back-arrow">← Regresar</a>

<?php if ($mensaje): ?>
    <p style="text-align:center; background-color:#fff5e6; border:1px solid #deb887; padding:10px; border-radius:8px; width:60%; margin:10px auto;">
        <?= htmlspecialchars($mensaje) ?>
    </p>
<?php endif; ?>

<div class="container">

    <!-- 📝 Formulario -->
    <div class="materias">
        <form method="POST" action="">
            <h1><?= $materiaEditar ? "Editar Materia" : "Agregar Materia" ?></h1>

            <input type="hidden" name="accion" value="<?= $materiaEditar ? "actualizar" : "agregar" ?>">
            <?php if ($materiaEditar): ?>
                <input type="hidden" name="id_materia" value="<?= htmlspecialchars($materiaEditar['id_materia']) ?>">
            <?php endif; ?>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required
                value="<?= $materiaEditar ? htmlspecialchars($materiaEditar['nombre']) : '' ?>">

            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" id="descripcion" required
                value="<?= $materiaEditar ? htmlspecialchars($materiaEditar['descripcion']) : '' ?>">

            <input type="submit" value="<?= $materiaEditar ? "Actualizar Materia" : "Agregar Materia" ?>">
        </form>
    </div>

    <!-- 📋 Tabla -->
    <div class="consultar">
        <h2>Listado de Materias</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($listaMaterias): ?>
                    <?php foreach ($listaMaterias as $mat): ?>
                        <tr>
                            <td><?= htmlspecialchars($mat['id_materia']) ?></td>
                            <td><?= htmlspecialchars($mat['nombre']) ?></td>
                            <td><?= htmlspecialchars($mat['descripcion']) ?></td>
                            <td style="text-align:center;">
                                <a href="?editar=<?= $mat['id_materia'] ?>" class="btn-accion" > Editar</a>
                                <a href="?eliminar=<?= $mat['id_materia'] ?>" class="btn-accion"
                                   onclick="return confirm('¿Seguro que deseas eliminar esta materia?')"> Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No hay materias registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
