<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/materias.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
</head>
<center>
    <br><br><br><br><br><br><br>
<?php 
include_once(__DIR__ . '../../assets/sentenciasSQL/Conexion.php');

$grupos = [];
if (isset($pdo)) {
    $stmt = $pdo->query("SELECT idGrupo, nombre FROM grupo");
    if ($stmt) {
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Materia {
    function Ingresar($nombre, $descripcion, $idGrupo){
        include_once(__DIR__ . '../../assets/sentenciasSQL/Conexion.php');

        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO materias (nombre, descripcion, idGrupo) 
                                   VALUES(:nombre, :descripcion, :idGrupo)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':idGrupo', $idGrupo, PDO::PARAM_INT);

            if($stmt->execute()) {
                echo "<p class='success'>Materia ingresada correctamente.</p>";
            } else {
                echo "<p class='error'>Error al ingresar la materia.</p>";
            }
        } else {
            echo "<p class='error'>Error de conexión a la base de datos.</p>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre'], $_POST['descripcion'], $_POST['idGrupo'])) {
        $materia = new Materia();
        $materia->Ingresar($_POST['nombre'], $_POST['descripcion'], $_POST['idGrupo']);
    } else {
        echo "<p class='error'>Faltan datos.</p>";
    }
}
?>

<!-- Caja con estilos desde el CSS externo -->
     <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

<div class="materias">
    <form action="materias.php" method="POST">
        <h1>Agregar Materia</h1>
        
        <label for="nombre">Nombre de la materia:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" required>

        <label for="idGrupo">Seleccionar Grupo:</label>
        <select name="idGrupo" id="idGrupo" required>
            <option value="">Seleccione un grupo</option>
            <?php foreach ($grupos as $grupo): ?>
                <option value="<?= htmlspecialchars($grupo['idGrupo']) ?>">
                    <?= htmlspecialchars($grupo['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <input type="submit" value="Enviar">
    </form>
</div>
</center>