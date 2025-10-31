<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

// Conexión a la base de datos
include_once(__DIR__ . '/../assets/sentenciasSQL/Conexion.php');

// Validar parámetro de grupo
if (!isset($_GET['idGrupo'])) {
    echo "<script>alert('No se ha especificado el grupo.'); window.location='gruposCreados.php';</script>";
    exit;
}

$idGrupo = intval($_GET['idGrupo']);

// Consultar materias relacionadas al grupo
$sql = "SELECT m.id_materia, m.nombre, m.descripcion 
        FROM grupo_materia gm
        INNER JOIN materias m ON gm.id_materia = m.id_materia
        WHERE gm.id_grupo = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idGrupo]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materias del Grupo</title>
    <link rel="stylesheet" href="css/materias.css?v=2.1">
</head>
<style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0e8dc;
    margin: 0;
    padding: 20px;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #8b4513;
}

.container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.card {
    background-color: #fffaf0;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.card h2 {
    margin: 0 0 10px;
    color: #8b4513;
}

.card p {
    margin: 5px 0;
    color: #5c4033;
}

.btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 12px;
    background: #a0522d;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    transition: background 0.3s;
}

.btn:hover {
    background: #8b4513;
}

.back-arrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: #a0522d;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    transition: color 0.3s ease, transform 0.2s ease;
}

.back-arrow:hover {
    color: #deb887;
    transform: translateX(-3px);
}

/* 🌙 Modo oscuro */
body.dark-mode {
    background-color: #2c2c2c;
    color: #f0e8dc;
}

body.dark-mode .card {
    background-color: #4a4a4a;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

body.dark-mode h1 {
    color: #ffd39b;
}

body.dark-mode .card h2 {
    color: #ffdead;
}

body.dark-mode .card p {
    color: #f5deb3;
}

body.dark-mode .btn {
    background-color: #6b4c2a;
    color: #fff1d0;
}

body.dark-mode .btn:hover {
    background-color: #8b5a2b;
}

body.dark-mode .back-arrow {
    color: #deb887;
}

body.dark-mode .back-arrow:hover {
    color: #fff1d0;
}

    </style>
<body>
    <a href="gruposCreados.php" class="back-arrow">&#8592; Regresar</a>

    <h1>Materias Asignadas al Grupo</h1>

    <div class="container">
        <?php if (!empty($materias)): ?>
            <?php foreach ($materias as $materia): ?>
                <div class="card">
                    <h2><?= htmlspecialchars($materia['nombre']) ?></h2>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($materia['descripcion']) ?></p>
                    <a class="btn" href="listaAlumnos.php?idMateria=<?= $materia['id_materia'] ?>&idGrupo=<?= $idGrupo ?>">
                        Ver alumnos
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay materias asignadas a este grupo.</p>
        <?php endif; ?>
    </div>
</body>
</html>
