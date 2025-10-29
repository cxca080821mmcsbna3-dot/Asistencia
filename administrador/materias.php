<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

// Clase para manejar materias
class Materia {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function Ingresar($nombre, $descripcion) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("INSERT INTO materias (nombre, descripcion) VALUES(:nombre, :descripcion)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);

            if ($stmt->execute()) {
                echo "<p>✅ Materia ingresada correctamente.</p>";
            } else {
                echo "<p>❌ Error al ingresar la materia.</p>";
            }
        } else {
            echo "<p>⚠️ Error de conexión a la base de datos.</p>";
        }
    }

    public function Consultar() {
        $lista = [];
        if ($this->pdo) {
            $stmt = $this->pdo->query("SELECT id_materia, nombre, descripcion FROM materias");
            if ($stmt) {
                $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $lista;
    }
}

// Cargar conexión
include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');

$materia = new Materia(isset($pdo) ? $pdo : null);

// Procesar formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre'], $_POST['descripcion'])) {
        $materia->Ingresar($_POST['nombre'], $_POST['descripcion']);
    } else {
        echo "<p>❌ Faltan datos.</p>";
    }
}

// Consultar todas las materias
$listaMaterias = $materia->Consultar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/materias.css?v=2.1">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<body>
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

    <center>
        <br><br><br><br><br>

        <div class="formulario">
            <h2>Registrar Nueva Materia</h2>
            <form method="POST" action="">
                <input type="text" name="nombre" placeholder="Nombre de la materia" required><br><br>
                <input type="text" name="descripcion" placeholder="Descripción" required><br><br>
                <input type="submit" value="Registrar">
            </form>
        </div>

        <div class="consultar">
            <h2>Listado de Materias</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($listaMaterias) > 0): ?>
                        <?php foreach ($listaMaterias as $materia): ?>
                            <tr>
                                <td><?= htmlspecialchars($materia['id_materia']) ?></td>
                                <td><?= htmlspecialchars($materia['nombre']) ?></td>
                                <td><?= htmlspecialchars($materia['descripcion']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No hay materias registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </center>
</body>
</html>
