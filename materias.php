<?php 
class Materia {
    function Ingresar($nombre, $descripcion){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO materias (nombre, descripcion) 
                                   VALUES(:nombre, :descripcion)");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);

            if($stmt->execute()) {
                echo "Materia ingresada correctamente.";
            } else {
                echo "Error al ingresar la materia.";
            }
        } else {
            echo "Error de conexión a la base de datos.";
        }
    }

    function Consultar(){
        include __DIR__ . '/assets/sentenciasSQL/Conexion.php';
        $lista = [];
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT id_materia, nombre, descripcion FROM materias");
            if($stmt){
                $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $lista;
    }
}

$materia = new Materia();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['descripcion'])) {
        $materia->Ingresar($_POST['nombre'], $_POST['descripcion']);
    } else {
        echo "Faltan datos.";
    }
}

$listaMaterias = $materia->Consultar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="assets/css/materias.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<body>
    <div class="container">
        <div class="materias">
            <form action="materias.php" method="POST">
                <h1>Agregar Materia</h1>
                <label for="nombre">Nombre de la materia:</label>
                <input type="text" name="nombre" id="nombre" required>
                
                <label for="descripcion">Descripción:</label>
                <input type="text" name="descripcion" id="descripcion" required>
                
                <input type="submit" value="Enviar">
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
    </div>
</body>
</html>
