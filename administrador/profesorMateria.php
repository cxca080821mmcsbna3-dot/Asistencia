<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');

$idClase = $_GET['idClase'] ?? null;

if (!$idClase) {
    echo "Clase no especificada";
    exit;
}

/* Obtener datos de la clase */
$sql = "SELECT 
            gm.id_clase,
            m.nombre AS materia,
            g.nombre AS grupo,
            p.id_profesor,
            p.nombre AS profesor
        FROM grupo_materia gm
        INNER JOIN materias m ON gm.id_materia = m.id_materia
        INNER JOIN grupo g ON gm.id_grupo = g.idGrupo
        LEFT JOIN profesor p ON gm.id_profesor = p.id_profesor
        WHERE gm.id_clase = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idClase]);
$clase = $stmt->fetch(PDO::FETCH_ASSOC);

/* Obtener todos los profesores */
$profesores = $pdo->query("SELECT id_profesor, nombre FROM profesor")->fetchAll(PDO::FETCH_ASSOC);

/* Actualizar profesor */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoProfesor = $_POST['id_profesor'];

    $update = $pdo->prepare(
        "UPDATE grupo_materia 
         SET id_profesor = ? 
         WHERE id_clase = ?"
    );
    $update->execute([$nuevoProfesor, $idClase]);

    header("Location: profesorMateria.php?idClase=$idClase");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Profesor asignado</title>
    <style>
        body {
            font-family: Arial;
            background: #f0e8dc;
            padding: 30px;
        }
        .card {
            max-width: 500px;
            margin: auto;
            background: #fffaf0;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,.1);
        }
        h2 { color: #8b4513; }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
        }
        button {
            background: #a0522d;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #8b4513;
        }
        .back {
  top: 12px;
  right: 15px;
  background-color: #a0522d;
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  padding: 8px 14px;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}
.back:hover {
  background-color: #deb887;
  color: #4b2e05;
}
    </style>
</head>
<body>

<div class="card">
    <a class="btn back" href="gruposCreados.php">&#8592; Volver</a>
    <h2><?= htmlspecialchars($clase['materia']) ?></h2>
    <p><strong>Grupo:</strong> <?= htmlspecialchars($clase['grupo']) ?></p>

    <p><strong>Profesor actual:</strong><br>
        <?= $clase['profesor'] ?? 'Sin asignar' ?>
    </p>

    <form method="POST">
        <label>Reasignar / Cambiar profesor:</label>
        <select name="id_profesor" required>
            <?php foreach ($profesores as $p): ?>
                <option value="<?= $p['id_profesor'] ?>"
                    <?= $p['id_profesor'] == $clase['id_profesor'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Guardar cambios</button>
    </form>
</div>

</body>
</html>
