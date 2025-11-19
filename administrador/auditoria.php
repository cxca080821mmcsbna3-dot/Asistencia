<?php
session_start();

// ✅ Verificación de acceso (solo administrador)
if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

// ✅ Conexión (usando tu mismo formato)
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
    // Fuerza UTF-8
    $pdo->exec("SET NAMES utf8mb4");

    // 🔹 IMPORTANTE: enviar el usuario logeado a MySQL
    if (!empty($_SESSION['nombre'])) {
        $usuario = $_SESSION['nombre'];
        $stmt = $pdo->prepare("SET @usuario_logeado = :usuario");
        $stmt->execute(['usuario' => $usuario]);
    } else {
        $pdo->exec("SET @usuario_logeado = NULL");
    }

} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// ✅ Consulta registros de auditoría
$stmt = $pdo->prepare("SELECT * FROM auditoria ORDER BY fecha DESC LIMIT 100");
$stmt->execute();
$registros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registros de Auditoría</title>
    <link rel="stylesheet" href="css/auditoria.css?v=1.2">
</head>
<body>
    <a href="menuGrupos.php" class="back-arrow">&#8592; Regresar</a>
  <div class="wrapper">
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
                    <td><?= htmlspecialchars($reg['usuario'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($reg['fecha']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</body>
</html>
