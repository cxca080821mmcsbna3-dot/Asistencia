<?php 
class Materia {
    function Ingresar($nombre, $descripcion, $idGrupo){
        include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO materias (nombre, descripcion, idGrupo) 
                                   VALUES(:nombre, :descripcion, :idGrupo)");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':idGrupo', $idGrupo, PDO::PARAM_INT);

            if($stmt->execute()) {
                echo "Materia ingresada correctamente.";
            } else {
                echo "Error al ingresar la materia.";
            }
        } else {
            echo "Error de conexión a la base de datos.";
        }
    }

    function Consultar($idGrupo){
        include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');
        $lista = [];
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT id_materia, nombre, descripcion FROM materias WHERE idGrupo = :idGrupo");
            $stmt->bindParam(':idGrupo', $idGrupo, PDO::PARAM_INT);
            if($stmt->execute()){
                $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $lista;
    }
}

// Obtener conexión y grupos
include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');
$grupos = [];
if (isset($pdo)) {
    $stmt = $pdo->query("SELECT idGrupo, nombre FROM grupo");
    if ($stmt) {
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$materia = new Materia();

// Procesar formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre'], $_POST['descripcion'], $_POST['idGrupo'])) {
        $materia->Ingresar($_POST['nombre'], $_POST['descripcion'], $_POST['idGrupo']);
    } else {
        echo "Faltan datos.";
    }
}

// Consultar materias por grupo si hay filtro
$listaMaterias = [];
if (isset($_GET['filtro_grupo'])) {
    $listaMaterias = $materia->Consultar($_GET['filtro_grupo']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/materias.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<body>
    <center>
    <br><br><br><br><br>

        <!-- FILTRO DE CONSULTA POR GRUPO -->
        <div class="consultar">
            <h2>Listado de Materias</h2>
            
            <form method="GET" action="materias.php">
                <label for="filtro_grupo">Filtrar por Grupo:</label>
                <select name="filtro_grupo" id="filtro_grupo" required>
                    <option value="">Seleccione un grupo</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?= htmlspecialchars($grupo['idGrupo']) ?>"
                            <?= (isset($_GET['filtro_grupo']) && $_GET['filtro_grupo'] == $grupo['idGrupo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($grupo['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="Filtrar">
            </form>

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
                        <tr><td colspan="3">No hay materias registradas para este grupo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</center>
</html>
