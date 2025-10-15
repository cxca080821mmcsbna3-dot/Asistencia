<?php
require_once __DIR__ . "/../assets/sentenciasSQL/grupos.php";

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
    <link rel="stylesheet" href="../assets/css/grupos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f6f9;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .card h2 {
            margin: 0 0 10px;
            color: #333;
        }
        .card p {
            margin: 5px 0;
            color: #555;
        }
        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .panel-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 12px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .panel-link:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <header>
    <h1>Lista de Grupos</h1>
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

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
