<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";
require_once __DIR__ . "/../assets/sentenciasSQL/funciones_seguridad.php";

if (!isset($_SESSION['ALUMNO'])) {
    header("Location: ../index.php");
    exit();
}

$idAlumno = $_SESSION['ALUMNO']['idAlumno'];
$mensajeError = null;

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
        $mensajeError = "Alumno no encontrado";
    }

    if (!$mensajeError) {
        $emailHash = md5(strtolower(trim($alumno['curp'])));
        $avatarUrl = "https://www.gravatar.com/avatar/$emailHash?s=200&d=identicon";

        $resumenInasistencias = obtenerResumenInasistenciasPorMateria($pdo, $idAlumno);
        $totalInasistencias = obtenerTotalInasistencias($pdo, $idAlumno);
    }

} catch (PDOException $e) {
    $mensajeError = "Error al consultar la base de datos";
    error_log("Error en perfil.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil del Alumno</title>
<?php echo estilosMensajes(); ?>
<link rel="stylesheet" href="css/perfil.css?v=3.0">
</head>
<body>

<div class="wrapper">
<?php if ($mensajeError): ?>
    <?php mostrarMensajeError("❌ " . $mensajeError, "No se puede cargar tu perfil en este momento."); ?>
    <a href="index.php" class="back-arrow">← Regresar</a>
<?php else: ?>

<a href="index.php" class="back-arrow">&#8592; Regresar</a>

<div class="perfil-tarjeta">
    <div class="perfil-imagen">
        <img src="<?= $avatarUrl ?>" alt="Avatar">
    </div>

    <div class="perfil-nombre">
        <h2><?= htmlspecialchars($alumno['nombre'].' '.$alumno['apellidos']) ?></h2>
        <p>
            Grupo: <?= htmlspecialchars($alumno['nombre_grupo'] ?? 'Sin grupo') ?>
            | Matrícula: <?= htmlspecialchars($alumno['matricula']) ?>
        </p>
    </div>

    <div class="perfil-body">

        <div class="perfil-seccion">
            <h3>Datos del Alumno</h3>
            <p><strong>CURP:</strong> <?= htmlspecialchars($alumno['curp']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($alumno['telefono']) ?></p>
        </div>

        <div class="perfil-seccion inasistencias-seccion">
            <h3>📊 Resumen de Inasistencias</h3>

            <div class="inasistencias-total">
                <div class="total-badge">
                    <span class="numero"><?= $totalInasistencias ?></span>
                    <span class="label">Total</span>
                </div>
            </div>

            <?php if (count($resumenInasistencias) > 0): ?>

            <!-- MOBILE -->
            <div class="inasistencias-mobile">
                <select id="selectMateria">
                    <?php foreach($resumenInasistencias as $index => $materia): ?>
                        <option value="<?= $index ?>">
                            <?= htmlspecialchars($materia['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div id="materiaDetalle"></div>
            </div>

            <!-- DESKTOP -->
            <table class="inasistencias-tabla">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Ausentes</th>
                        <th>Retardos</th>
                        <th>Justificantes</th>
                        <th>Total</th>
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

            <script>
                const materiasData = <?= json_encode($resumenInasistencias) ?>;
            </script>

            <?php else: ?>
                <p class="sin-inasistencias">✅ No tienes inasistencias.</p>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

  if (localStorage.getItem("modo") === "oscuro") {
    document.body.classList.add("dark-mode");
  }

  const select = document.getElementById("selectMateria");
  const detalle = document.getElementById("materiaDetalle");

  if (select) {

    function mostrarMateria(index) {
      const materia = materiasData[index];

      detalle.innerHTML = `
        <div class="card-materia">
          <h4>${materia.nombre}</h4>
          <p><strong class="ausentes">Ausentes:</strong> ${materia.inasistencias}</p>
          <p><strong class="retardos">Retardos:</strong> ${materia.retardos}</p>
          <p><strong class="justificantes">Justificantes:</strong> ${materia.justificantes}</p>
          <p><strong>Total:</strong> ${materia.total_registros}</p>
        </div>
      `;
    }

    mostrarMateria(0);

    select.addEventListener("change", function() {
      mostrarMateria(this.value);
    });
  }

});
</script>

</body>
</html>