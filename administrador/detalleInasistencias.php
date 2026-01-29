<?php
/**
 * ARCHIVO: detalleInasistencias.php
 * UBICACIÓN: administrador/detalleInasistencias.php
 * PROPÓSITO: Mostrar detalle completo de inasistencias de un alumno en una materia
 * CREADO: 20 de enero de 2026
 */

session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";
require_once __DIR__ . "/../assets/sentenciasSQL/funciones_seguridad.php";

// 🔐 CORRECCIÓN #3: Validar que es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// --- Validaciones ---
if (!isset($_GET['idAlumno']) || !isset($_GET['idMateria'])) {
    header("Location: materias.php");
    exit();
}

// 🔐 CORRECCIÓN #4: Validar IDs
$idAlumno = intval($_GET['idAlumno'] ?? 0);
$idMateria = intval($_GET['idMateria'] ?? 0);

// Validar que los IDs sean válidos
$mensajeError = null;
if ($idAlumno <= 0 || $idMateria <= 0) {
    $mensajeError = "Parámetros inválidos";
}

// Validar que el alumno existe
if (!$mensajeError) {
    $stmtValida = $pdo->prepare("SELECT id_alumno FROM alumno WHERE id_alumno = ?");
    $stmtValida->execute([$idAlumno]);
    if (!$stmtValida->fetch()) {
        $mensajeError = "Alumno no encontrado";
    }
}

// Validar que la materia existe
if (!$mensajeError) {
    $stmtValida = $pdo->prepare("SELECT id_materia FROM materias WHERE id_materia = ?");
    $stmtValida->execute([$idMateria]);
    if (!$stmtValida->fetch()) {
        $mensajeError = "Materia no encontrada";
    }
}

// --- Obtener datos del alumno ---
try {
    if (!$mensajeError) {
        $stmt = $pdo->prepare("
            SELECT a.id_alumno, a.nombre, a.apellidos, a.matricula, a.numero_lista,
                   g.nombre AS nombre_grupo
            FROM alumno a
            LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
            WHERE a.id_alumno = :idAlumno
            LIMIT 1
        ");
        $stmt->execute([':idAlumno' => $idAlumno]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$alumno) {
            $mensajeError = "Alumno no encontrado";
        }

        // --- Obtener datos de la materia ---
        if (!$mensajeError) {
            $stmt = $pdo->prepare("
                SELECT id_materia, nombre
                FROM materias
                WHERE id_materia = :idMateria
                LIMIT 1
            ");
            $stmt->execute([':idMateria' => $idMateria]);
            $materia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$materia) {
                $mensajeError = "Materia no encontrada";
            }
        }
    }

    if (!$mensajeError) {
        // --- Obtener inasistencias en esta materia ---
        $historial = obtenerHistorialInasistencias($pdo, $idAlumno, $idMateria);
        $inasistencias = obtenerInasistenciasPorMateria($pdo, $idAlumno, $idMateria);
        
        // CORRECCIÓN #6: Simplificar consulta (eliminar window functions innecesarias)
        $stmt = $pdo->prepare("
            SELECT fecha, estado
            FROM asistencia
            WHERE id_alumno = :idAlumno AND id_materia = :idMateria
            ORDER BY fecha DESC
        ");
        $stmt->execute([':idAlumno' => $idAlumno, ':idMateria' => $idMateria]);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Contar estados
        $estadisticas = [
            'ausentes' => 0,
            'retardos' => 0,
            'justificantes' => 0,
            'presentes' => 0,
            'total' => 0
        ];

        foreach ($registros as $r) {
            $estadisticas['total']++;
            if ($r['estado'] == 'Ausente') $estadisticas['ausentes']++;
            elseif ($r['estado'] == 'Retardo') $estadisticas['retardos']++;
            elseif ($r['estado'] == 'Justificante') $estadisticas['justificantes']++;
            else $estadisticas['presentes']++;
        }

        // 📊 NUEVO: Obtener resumen de inasistencias en TODAS las materias
        $resumenTodasMaterias = obtenerResumenInasistenciasPorMateria($pdo, $idAlumno);

        // CORRECCIÓN #7: Contar TODAS las inasistencias (Ausente, Retardo, Justificante)
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT id_materia) as total_materias
            FROM asistencia
            WHERE id_alumno = :idAlumno AND estado IN ('Ausente', 'Retardo', 'Justificante')
        ");
        $stmt->execute([':idAlumno' => $idAlumno]);
        $totalMaterias = intval($stmt->fetchColumn() ?? 0);

        // CORRECCIÓN #7: Contar días únicos con TODAS las inasistencias
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT fecha) as total_dias
            FROM asistencia
            WHERE id_alumno = :idAlumno AND estado IN ('Ausente', 'Retardo', 'Justificante')
        ");
        $stmt->execute([':idAlumno' => $idAlumno]);
        $totalDias = intval($stmt->fetchColumn() ?? 0);
    }

} catch (PDOException $e) {
    $mensajeError = "Error al consultar la base de datos";
    error_log("Error en detalleInasistencias: " . $e->getMessage());
}

// --- Meses en español ---
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Función para obtener color según estado
function getColorEstado($estado) {
    switch($estado) {
        case 'Ausente': return '#ff6b6b';
        case 'Retardo': return '#ffa500';
        case 'Justificante': return '#4da6ff';
        case 'Presente': return '#4caf50';
        default: return '#e0e0e0';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle de Inasistencias</title>
<?php echo estilosMensajes(); ?>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0e8dc;
    padding: 20px;
    display: flex;
    justify-content: center;
}

.container {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 900px;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #a0522d;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
}

.back-link:hover {
    color: #deb887;
}

h1 {
    color: #8b4513;
    margin-bottom: 10px;
    font-size: 2rem;
}

.alumno-info {
    background: linear-gradient(135deg, #fffaf0 0%, #fff5e6 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    border-left: 5px solid #a0522d;
}

.alumno-info h2 {
    color: #8b4513;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.alumno-info p {
    color: #5c4033;
    margin: 5px 0;
    font-size: 1rem;
}

.materia-badge {
    display: inline-block;
    background: #d2b48c;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    margin-top: 10px;
    font-weight: bold;
}

.estadisticas {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.estadistica-card {
    background: white;
    border: 2px solid #ddd;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    transition: transform 0.3s;
}

.estadistica-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.estadistica-card.ausentes {
    border-color: #ff6b6b;
    background: #ffebee;
}

.estadistica-card.retardos {
    border-color: #ffa500;
    background: #fff3e0;
}

.estadistica-card.justificantes {
    border-color: #4da6ff;
    background: #e3f2fd;
}

.estadistica-card.presentes {
    border-color: #4caf50;
    background: #f1f8f4;
}

.numero {
    display: block;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.ausentes .numero { color: #d32f2f; }
.retardos .numero { color: #f57c00; }
.justificantes .numero { color: #1976d2; }
.presentes .numero { color: #388e3c; }

.label {
    display: block;
    font-size: 0.9rem;
    color: #666;
    font-weight: 600;
}

h3 {
    color: #8b4513;
    margin-top: 30px;
    margin-bottom: 15px;
    font-size: 1.3rem;
    border-bottom: 3px solid #d2b48c;
    padding-bottom: 10px;
}

.historial-tabla {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.historial-tabla thead {
    background: #d2b48c;
    color: white;
}

.historial-tabla th {
    padding: 12px;
    text-align: left;
    font-weight: bold;
}

.historial-tabla td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.historial-tabla tbody tr:hover {
    background-color: #f9f9f9;
}

.estado-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: bold;
    color: white;
}

.estado-badge.ausente {
    background: #ff6b6b;
}

.estado-badge.retardo {
    background: #ffa500;
}

.estado-badge.justificante {
    background: #4da6ff;
}

.estado-badge.presente {
    background: #4caf50;
}

.sin-datos {
    background: #f9f9f9;
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    color: #999;
}

.fecha-fecha {
    color: #5c4033;
    font-weight: 500;
}

/* 📊 NUEVO: Estilos para tabla de resumen de materias */
.resumen-materias-tabla {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

body.dark-mode .resumen-materias-tabla {
    background: #3a3a3a;
}

.resumen-materias-tabla thead {
    background: #e8c8a8;
    color: white;
    font-weight: bold;
}

body.dark-mode .resumen-materias-tabla thead {
    background: #8b6914;
}

.resumen-materias-tabla th,
.resumen-materias-tabla td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}

body.dark-mode .resumen-materias-tabla td {
    color: #f0e8dc;
    border-bottom: 1px solid #555;
}

.resumen-materias-tabla tbody tr:hover {
    background-color: #f9f9f9;
}

body.dark-mode .resumen-materias-tabla tbody tr:hover {
    background-color: #454545;
}

.resumen-materias-tabla .materia-nombre {
    text-align: left;
    font-weight: 600;
    color: #4b3621;
}

body.dark-mode .resumen-materias-tabla .materia-nombre {
    color: #ffe4c4;
}

.resumen-materias-tabla .ausentes-col {
    background-color: #ffebee !important;
}

.resumen-materias-tabla .retardos-col {
    background-color: #fff3e0 !important;
}

.resumen-materias-tabla .justificantes-col {
    background-color: #e3f2fd !important;
}

/* 📊 NUEVO: Estilos para métricas de resumen global */
.metricas-resumen {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    margin: 20px 0 25px 0;
}

.metrica-card {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8a7b 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    color: white;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.25);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.metrica-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(255, 107, 107, 0.35);
}

.metrica-card:nth-child(2) {
    background: linear-gradient(135deg, #ffa500 0%, #ffb84d 100%);
    box-shadow: 0 4px 12px rgba(255, 165, 0, 0.25);
}

.metrica-card:nth-child(2):hover {
    box-shadow: 0 6px 16px rgba(255, 165, 0, 0.35);
}

.metrica-numero {
    font-size: 2.5rem;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

.metrica-label {
    font-size: 0.95rem;
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
}

.metrica-descripcion {
    font-size: 0.8rem;
    opacity: 0.85;
}

/* Modo oscuro para métricas */
body.dark-mode .metrica-card {
    background: linear-gradient(135deg, #c41e3a 0%, #d63447 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(196, 30, 58, 0.35);
}

body.dark-mode .metrica-card:nth-child(2) {
    background: linear-gradient(135deg, #b8860b 0%, #daa520 100%);
    box-shadow: 0 4px 12px rgba(184, 134, 11, 0.35);
}

body.dark-mode .metrica-card:hover {
    box-shadow: 0 6px 16px rgba(196, 30, 58, 0.45);
}

body.dark-mode .metrica-card:nth-child(2):hover {
    box-shadow: 0 6px 16px rgba(184, 134, 11, 0.45);
}

/* Modo oscuro */
body.dark-mode {
    background-color: #2c2c2c;
}

body.dark-mode .container {
    background-color: #3a3a3a;
    color: #f0e8dc;
}

body.dark-mode .back-link {
    color: #deb887;
}

body.dark-mode h1,
body.dark-mode h2,
body.dark-mode h3 {
    color: #ffdead;
}

body.dark-mode .alumno-info {
    background: #4a4a4a;
    border-left-color: #ffdead;
}

body.dark-mode .alumno-info p {
    color: #ffe4c4;
}

body.dark-mode .estadistica-card {
    background: #4a4a4a;
    border-color: #666;
}

body.dark-mode .historial-tabla thead {
    background: #8b6914;
}

body.dark-mode .historial-tabla td {
    border-bottom: 1px solid #555;
    color: #f0e8dc;
}

body.dark-mode .historial-tabla tbody tr:hover {
    background-color: #454545;
}

body.dark-mode .sin-datos {
    background: #4a4a4a;
    color: #999;
}

@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    h1 {
        font-size: 1.5rem;
    }

    .estadisticas {
        grid-template-columns: repeat(2, 1fr);
    }

    .historial-tabla th,
    .historial-tabla td {
        padding: 8px;
        font-size: 0.9rem;
    }
}
</style>
</head>
<body>

<div class="container">
    <?php if ($mensajeError): ?>
        <?php mostrarMensajeError("❌ " . $mensajeError, "No se puede mostrar el detalle de inasistencias. Verifique los parámetros."); ?>
        <a href="materias.php" class="back-link">← Volver a Materias</a>
    <?php else: ?>
    
    <a href="javascript:history.back()" class="back-link">&#8592; Regresar</a>

    <h1>📊 Detalle de Inasistencias</h1>

    <div class="alumno-info">
        <h2><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></h2>
        <p><strong>Matrícula:</strong> <?= htmlspecialchars($alumno['matricula']) ?></p>
        <p><strong>Grupo:</strong> <?= htmlspecialchars($alumno['nombre_grupo'] ?? 'Sin grupo') ?></p>
        <span class="materia-badge"><?= htmlspecialchars($materia['nombre']) ?></span>
    </div>

    <div class="estadisticas">
        <div class="estadistica-card ausentes">
            <span class="numero"><?= $estadisticas['ausentes'] ?></span>
            <span class="label">Ausencias</span>
        </div>
        <div class="estadistica-card retardos">
            <span class="numero"><?= $estadisticas['retardos'] ?></span>
            <span class="label">Retardos</span>
        </div>
        <div class="estadistica-card justificantes">
            <span class="numero"><?= $estadisticas['justificantes'] ?></span>
            <span class="label">Justificantes</span>
        </div>
        <div class="estadistica-card presentes">
            <span class="numero"><?= $estadisticas['presentes'] ?></span>
            <span class="label">Presencias</span>
        </div>
    </div>

    <h3>📋 Historial Completo</h3>

    <?php if (count($registros) > 0): ?>
    <table class="historial-tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
            <tr>
                <td class="fecha-fecha">
                    <?php 
                    $fecha = new DateTime($registro['fecha']);
                    $nombreMes = $meses[$fecha->format('n')];
                    echo $fecha->format('d') . ' de ' . $nombreMes . ' de ' . $fecha->format('Y');
                    ?>
                </td>
                <td>
                    <span class="estado-badge <?= strtolower(str_replace(' ', '', $registro['estado'])) ?>">
                        <?= htmlspecialchars($registro['estado']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="sin-datos">
        <p>✅ No hay registros de asistencia para este alumno en esta materia.</p>
    </div>
    <?php endif; ?>

    <!-- 📊 NUEVO: Resumen de inasistencias en TODAS las materias -->
    <h3>📚 Resumen de Inasistencias en Todas las Materias</h3>
    
    <!-- Métricas de resumen global -->
    <div class="metricas-resumen">
        <div class="metrica-card">
            <div class="metrica-numero"><?= $totalMaterias ?></div>
            <div class="metrica-label">Materias con Inasistencias</div>
            <div class="metrica-descripcion">Número de materias diferentes</div>
        </div>
        <div class="metrica-card">
            <div class="metrica-numero"><?= $totalDias ?></div>
            <div class="metrica-label">Días con Inasistencias</div>
            <div class="metrica-descripcion">Número de días diferentes</div>
        </div>
    </div>
    
    <?php if (count($resumenTodasMaterias) > 0): ?>
    <table class="resumen-materias-tabla">
        <thead>
            <tr>
                <th>Materia</th>
                <th style="background-color: #ffcccc;">🔴 Ausentes</th>
                <th style="background-color: #ffe6cc;">🟠 Retardos</th>
                <th style="background-color: #ccddff;">🔵 Justificantes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resumenTodasMaterias as $m): ?>
            <tr>
                <td class="materia-nombre"><?= htmlspecialchars($m['nombre']) ?></td>
                <td class="ausentes-col" style="color: #d32f2f; font-weight: bold;"><?= $m['inasistencias'] ?></td>
                <td class="retardos-col" style="color: #f57c00; font-weight: bold;"><?= $m['retardos'] ?></td>
                <td class="justificantes-col" style="color: #1976d2; font-weight: bold;"><?= $m['justificantes'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="sin-datos">
        <p>✅ No hay registros de asistencia en ninguna materia.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
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
