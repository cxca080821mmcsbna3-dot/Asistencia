<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

// Bloqueo: solo alumnos
if (!isset($_SESSION['idAlumno']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../index.php");
    exit;
}

$idAlumno = $_SESSION['idAlumno'];

try {
    $stmt = $pdo->prepare("
        SELECT id_alumno, nombre, apellidos, matricula, curp, telefono, id_grupo
        FROM alumno
        WHERE id_alumno = :idAlumno
    ");
    $stmt->execute(['idAlumno' => $idAlumno]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) die("❌ Alumno no encontrado");

    // Gravatar basado en curp
    $emailHash = md5(strtolower(trim($alumno['curp'])));
    $avatarUrl = "https://www.gravatar.com/avatar/$emailHash?s=200&d=identicon";

} catch (PDOException $e) {
    die("❌ Error al consultar la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil del Alumno</title>
<link rel="stylesheet" href="css/perfil.css?v=2.1"> <!-- mismo estilo que asistencias -->

</head>
<body>
<div class="wrapper">
<a href="menu_alumno.php" class="back-arrow">&#8592; Regresar</a>

<div class="perfil-tarjeta">
    <div class="perfil-imagen">
        <img src="<?= $avatarUrl ?>" alt="Avatar del Alumno">
    </div>
    <div class="perfil-nombre">
        <h2><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></h2>
        <p>ID Grupo: <?= htmlspecialchars($alumno['id_grupo']) ?> | Matrícula: <?= htmlspecialchars($alumno['matricula']) ?></p>
    </div>

    <div class="perfil-body">
        <div class="perfil-seccion">
            <h3>Datos del Alumno</h3>
            <p><strong>CURP:</strong> <?= htmlspecialchars($alumno['curp']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($alumno['telefono']) ?></p>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("modo") === "oscuro") {
    document.body.classList.add("dark-mode");
  }
});
</script>
</body>
</html>
