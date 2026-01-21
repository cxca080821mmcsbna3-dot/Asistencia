<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";

// 🔐 Bloqueo: SOLO alumnos (sesión nueva)
if (!isset($_SESSION['ALUMNO'])) {
    header("Location: ../index.php");
    exit();
}

$idAlumno = $_SESSION['ALUMNO']['idAlumno'];

try {
    $stmt = $pdo->prepare("
        SELECT a.id_alumno, a.nombre, a.apellidos, a.matricula, a.curp, a.telefono, a.id_grupo,
               g.nombre AS nombre_grupo
        FROM alumno a
        LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
        WHERE a.id_alumno = :idAlumno
        LIMIT 1
    ");
    $stmt->execute(['idAlumno' => $idAlumno]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) {
        die("❌ Alumno no encontrado");
    }

    // Gravatar basado en CURP
    $emailHash = md5(strtolower(trim($alumno['curp'])));
    $avatarUrl = "https://www.gravatar.com/avatar/$emailHash?s=200&d=identicon";

    // 📊 NUEVO: Obtener resumen de inasistencias por materia
    $resumenInasistencias = obtenerResumenInasistenciasPorMateria($pdo, $idAlumno);
    $totalInasistencias = obtenerTotalInasistencias($pdo, $idAlumno);

} catch (PDOException $e) {
    die("❌ Error al consultar la base de datos");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil del Alumno</title>
<link rel="stylesheet" href="css/perfil.css?v=2.1">
</head>
<body>

<div class="wrapper">
<a href="index.php" class="back-arrow">&#8592; Regresar</a>

<div class="perfil-tarjeta">
    <div class="perfil-imagen">
        <img src="<?= $avatarUrl ?>" alt="Avatar del Alumno">
    </div>

    <div class="perfil-nombre">
        <h2><?= htmlspecialchars($alumno['nombre'].' '.$alumno['apellidos']) ?></h2>
        <p>
            Grupo: <?= htmlspecialchars($alumno['nombre_grupo'] ?? 'Sin grupo asignado') ?>
            | Matrícula: <?= htmlspecialchars($alumno['matricula']) ?>
        </p>
    </div>

    <div class="perfil-body">
        <div class="perfil-seccion">
            <h3>Datos del Alumno</h3>
            <p><strong>CURP:</strong> <?= htmlspecialchars($alumno['curp']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($alumno['telefono']) ?></p>
        </div>

        <!-- 📊 NUEVO: Sección de Inasistencias -->
        <div class="perfil-seccion inasistencias-seccion">
            <h3>📊 Resumen de Inasistencias</h3>
            <div class="inasistencias-total">
                <div class="total-badge">
                    <span class="numero"><?= $totalInasistencias ?></span>
                    <span class="label">Total de Inasistencias</span>
                </div>
            </div>

            <?php if (count($resumenInasistencias) > 0): ?>
            <h4>Por Materia:</h4>
            <table class="inasistencias-tabla">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Ausentes</th>
                        <th>Retardos</th>
                        <th>Justificantes</th>
                        <th>Total Registros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resumenInasistencias as $materia): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($materia['nombre']) ?></strong></td>
                        <td class="ausentes"><?= $materia['inasistencias'] ?></td>
                        <td class="retardos"><?= $materia['retardos'] ?></td>
                        <td class="justificantes"><?= $materia['justificantes'] ?></td>
                        <td><?= $materia['total_registros'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="sin-inasistencias">✅ No tienes registros de inasistencias aún.</p>
            <?php endif; ?>
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
