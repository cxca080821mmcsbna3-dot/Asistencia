<?php
require_once __DIR__ . "/../assets/sentenciasSQL/grupos.php";

session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

$grupos = new Grupos();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $idEliminar = intval($_POST['idGrupo']);
    if ($grupos->eliminarGrupo($idEliminar)) {
        echo "<script>alert('Evento eliminado correctamente'); window.location='gruposCreados.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el evento');</script>";
    }
}
$listaGrupos = $grupos->leerGrupos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos</title>
    <link rel="stylesheet" href="css/grupos.css?v=2.1">
</head>
<body>
    <header>
    <h1>Lista de Grupos</h1>
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>
    <a href="crearGrupo.php" class="back-arrow">Crear Grupo +</a>

    </header>

    <div class="container">
        <?php if (!empty($listaGrupos)): ?>
            <?php foreach ($listaGrupos as $grupos): ?>
                <div class="card">
                    <div class="headerCardGrupos">
                         <a href="editar_grupo.php?idGrupo=<?= $grupos['idGrupo']; ?>"><button>editar</button></a>
                        
                        <!-- Botón eliminar con confirmación -->
                        <form method="POST" style="display:inline;" 
                              onsubmit="return confirm('¿Estás seguro de eliminar este Grupo?');">
                            <input type="hidden" name="idGrupo" value="<?= $grupos['idGrupo']; ?>">
                            <button type="submit" name="eliminar">eliminar</button>
                        </form>
                    </div>

                    <h2><?= htmlspecialchars($grupos['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><strong>Descripcion:</strong> <?= htmlspecialchars($grupos['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Tutor:</strong> <?= htmlspecialchars($grupos['tutor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn" href="materias.php">Informacion del grupo</a>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay grupos registrados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
