<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../assets/sentenciasSQL/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Sin autorización']);
    exit;
}

$tipo   = $_GET['tipo']   ?? 'diaria';   // 'diaria' | 'materia'
$fecha  = $_GET['fecha']  ?? date('Y-m-d');
$grupo  = $_GET['grupo']  ?? '';
$estado = $_GET['estado'] ?? '';
$buscar = trim($_GET['buscar'] ?? '');
$mes    = $_GET['mes']    ?? '';         // para vista mensual materia: 'YYYY-MM'

try {
    // ════════════════════════════════════
    // VISTA: ASISTENCIA DIARIA GENERAL
    // Fuente: asistencia_diaria  (tabla nueva)
    // ════════════════════════════════════
    if ($tipo === 'diaria') {

        $where  = ['1=1'];
        $params = [];

        if ($fecha) {
            $where[] = 'ad.fecha = :fecha';
            $params[':fecha'] = $fecha;
        }
        if ($grupo) {
            $where[] = 'al.id_grupo = :grupo';
            $params[':grupo'] = (int)$grupo;
        }
        if ($estado) {
            $where[] = 'ad.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($buscar) {
            $where[] = '(al.matricula LIKE :b OR al.nombre LIKE :b OR al.apellidos LIKE :b)';
            $params[':b'] = "%$buscar%";
        }

        $w = implode(' AND ', $where);

        $stmt = $pdo->prepare("
            SELECT ad.id_asistencia_diaria,
                   al.matricula, al.nombre, al.apellidos,
                   g.nombre AS grupo,
                   ad.fecha, ad.hora_entrada, ad.estado, ad.dispositivo
            FROM asistencia_diaria ad
            JOIN alumno al ON ad.id_alumno = al.id_alumno
            LEFT JOIN grupo g ON al.id_grupo = g.idGrupo
            WHERE $w
            ORDER BY ad.fecha DESC, ad.hora_entrada DESC
            LIMIT 500
        ");
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats del día seleccionado
        $stmtS = $pdo->prepare("
            SELECT
                COUNT(*)                                        AS total,
                COUNT(CASE WHEN estado='Presente' THEN 1 END)  AS presentes,
                COUNT(CASE WHEN estado='Tardío'   THEN 1 END)  AS tardios
            FROM asistencia_diaria
            WHERE fecha = :f
        ");
        $stmtS->execute([':f' => $fecha ?: date('Y-m-d')]);
        $stats = $stmtS->fetch(PDO::FETCH_ASSOC);

        // Grupos para filtro
        $stmtG = $pdo->prepare("SELECT idGrupo, nombre FROM grupo ORDER BY nombre ASC");
        $stmtG->execute();
        $grupos = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'tipo'      => 'diaria',
            'registros' => $registros,
            'stats'     => $stats,
            'grupos'    => $grupos,
        ]);

    // ════════════════════════════════════
    // VISTA: ASISTENCIA POR MATERIA
    // Fuente: asistencia  (tabla original — sin modificar)
    // ════════════════════════════════════
    } elseif ($tipo === 'materia') {

        $where  = ['1=1'];
        $params = [];

        if ($fecha && !$mes) {
            $where[] = 'a.fecha = :fecha';
            $params[':fecha'] = $fecha;
        }
        if ($mes) {
            $where[] = 'a.fecha LIKE :mes';
            $params[':mes'] = $mes . '%';
        }
        if ($grupo) {
            $where[] = 'a.id_grupo = :grupo';
            $params[':grupo'] = (int)$grupo;
        }
        if ($estado) {
            $where[] = 'a.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($buscar) {
            $where[] = '(al.matricula LIKE :b OR al.nombre LIKE :b OR al.apellidos LIKE :b)';
            $params[':b'] = "%$buscar%";
        }

        $w = implode(' AND ', $where);

        $stmt = $pdo->prepare("
            SELECT a.id_asistencia,
                   al.matricula, al.nombre, al.apellidos,
                   g.nombre  AS grupo,
                   m.nombre  AS materia,
                   a.fecha, a.estado
            FROM asistencia a
            JOIN alumno  al ON a.id_alumno  = al.id_alumno
            JOIN materias m ON a.id_materia = m.id_materia
            LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
            WHERE $w
            ORDER BY a.fecha DESC, al.apellidos ASC
            LIMIT 500
        ");
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats de faltas por materia
        $stmtS = $pdo->prepare("
            SELECT
                COUNT(*)                                            AS total,
                COUNT(CASE WHEN a.estado='Ausente'      THEN 1 END) AS ausentes,
                COUNT(CASE WHEN a.estado='Retardo'      THEN 1 END) AS retardos,
                COUNT(CASE WHEN a.estado='Justificante' THEN 1 END) AS justificados
            FROM asistencia a
            WHERE a.fecha = :f
        ");
        $stmtS->execute([':f' => $fecha ?: date('Y-m-d')]);
        $stats = $stmtS->fetch(PDO::FETCH_ASSOC);

        // Materias para filtro
        $stmtM = $pdo->prepare("SELECT id_materia, nombre FROM materias ORDER BY nombre ASC");
        $stmtM->execute();
        $materias = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Grupos para filtro
        $stmtG = $pdo->prepare("SELECT idGrupo, nombre FROM grupo ORDER BY nombre ASC");
        $stmtG->execute();
        $grupos = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'tipo'      => 'materia',
            'registros' => $registros,
            'stats'     => $stats,
            'materias'  => $materias,
            'grupos'    => $grupos,
        ]);

    } else {
        echo json_encode(['error' => 'Tipo no válido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
}
