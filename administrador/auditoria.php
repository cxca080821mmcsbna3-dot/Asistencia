<?php
$host = 'localhost';
$db   = 'asistencia';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Consulta registros auditoria
$stmt = $pdo->prepare("SELECT * FROM auditoria ORDER BY fecha DESC LIMIT 100");
$stmt->execute();
$registros = $stmt->fetchAll();

session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/auditoria.css?v=1.2">
    <meta charset="UTF-8" />
    <title>Registros de Auditoría</title>
</head>
<body>
        <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>

    <table>
        <caption>Registros de Auditoría</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tabla Afectada</th>
                <th>ID Registro</th>
                <th>Acción</th>
                <th>Datos Antes</th>
                <th>Datos Después</th>
                <th>Usuario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($registros) === 0): ?>
                <tr><td colspan="8" style="text-align:center;">No hay registros.</td></tr>
            <?php else: ?>
                <?php foreach ($registros as $reg): ?>
                <tr>
                    <td><?= htmlspecialchars($reg['id_auditoria']) ?></td>
                    <td><?= htmlspecialchars($reg['tabla_afectada']) ?></td>
                    <td><?= htmlspecialchars($reg['id_registro']) ?></td>
                    <td><?= htmlspecialchars($reg['accion']) ?></td>
                    <td><?= nl2br(htmlspecialchars($reg['datos_antes'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($reg['datos_despues'])) ?></td>
                    <td><?= htmlspecialchars($reg['usuario']) ?></td>
                    <td><?= htmlspecialchars($reg['fecha']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
