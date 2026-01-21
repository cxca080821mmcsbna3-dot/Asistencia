<?php
/**
 * ARCHIVO: asistenciaFunciones.php
 * PROPÓSITO: Funciones reutilizables para cálculos de asistencias e inasistencias
 * CREADO: 20 de enero de 2026
 * 
 * Este archivo contiene funciones para:
 * - Contar inasistencias por alumno
 * - Obtener estadísticas de asistencia
 * - Detalles de inasistencias por materia
 */

/**
 * Obtiene el total de INASISTENCIAS (estado = 'Ausente') de un alumno en todas las materias
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @return int - Número total de inasistencias
 */
function obtenerTotalInasistencias($pdo, $id_alumno) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM asistencia
            WHERE id_alumno = :id_alumno AND estado = 'Ausente'
        ");
        $stmt->execute([':id_alumno' => $id_alumno]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Obtiene el total de inasistencias de un alumno por materia específica
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @param int $id_materia - ID de la materia
 * @return int - Número de inasistencias en esa materia
 */
function obtenerInasistenciasPorMateria($pdo, $id_alumno, $id_materia) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM asistencia
            WHERE id_alumno = :id_alumno 
              AND id_materia = :id_materia 
              AND estado = 'Ausente'
        ");
        $stmt->execute([
            ':id_alumno' => $id_alumno,
            ':id_materia' => $id_materia
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Obtiene un resumen completo de inasistencias por materia para un alumno
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @return array - Array con estructura: [['id_materia' => X, 'nombre' => Y, 'inasistencias' => Z], ...]
 */
function obtenerResumenInasistenciasPorMateria($pdo, $id_alumno) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.id_materia,
                m.nombre,
                COUNT(CASE WHEN a.estado = 'Ausente' THEN 1 END) as inasistencias,
                COUNT(CASE WHEN a.estado = 'Retardo' THEN 1 END) as retardos,
                COUNT(CASE WHEN a.estado = 'Justificante' THEN 1 END) as justificantes,
                COUNT(a.id_asistencia) as total_registros
            FROM materias m
            LEFT JOIN asistencia a ON m.id_materia = a.id_materia 
                                   AND a.id_alumno = :id_alumno
            WHERE m.id_materia IN (
                SELECT DISTINCT a2.id_materia 
                FROM asistencia a2 
                WHERE a2.id_alumno = :id_alumno
            )
            GROUP BY m.id_materia, m.nombre
            ORDER BY m.nombre ASC
        ");
        $stmt->execute([':id_alumno' => $id_alumno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtiene detalles completos de inasistencias por alumno para un grupo/materia específico
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_materia - ID de la materia
 * @param int $id_grupo - ID del grupo
 * @return array - Array con: [['id_alumno' => X, 'nombre' => Y, 'apellidos' => Z, 'inasistencias' => W], ...]
 */
function obtenerInasistenciasGrupoMateria($pdo, $id_materia, $id_grupo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id_alumno,
                a.matricula,
                a.nombre,
                a.apellidos,
                a.numero_lista,
                COUNT(CASE WHEN asi.estado = 'Ausente' THEN 1 END) as inasistencias,
                COUNT(CASE WHEN asi.estado = 'Retardo' THEN 1 END) as retardos,
                COUNT(CASE WHEN asi.estado = 'Justificante' THEN 1 END) as justificantes
            FROM alumno a
            LEFT JOIN asistencia asi ON a.id_alumno = asi.id_alumno 
                                     AND asi.id_materia = :id_materia
            WHERE a.id_grupo = :id_grupo
            GROUP BY a.id_alumno, a.matricula, a.nombre, a.apellidos, a.numero_lista
            ORDER BY a.numero_lista ASC
        ");
        $stmt->execute([
            ':id_materia' => $id_materia,
            ':id_grupo' => $id_grupo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtiene el historial de inasistencias de un alumno en una materia (detalles de fechas)
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @param int $id_materia - ID de la materia
 * @return array - Array con: [['fecha' => X, 'estado' => Y], ...]
 */
function obtenerHistorialInasistencias($pdo, $id_alumno, $id_materia) {
    try {
        $stmt = $pdo->prepare("
            SELECT fecha, estado
            FROM asistencia
            WHERE id_alumno = :id_alumno 
              AND id_materia = :id_materia
              AND estado != 'Presente'
            ORDER BY fecha DESC
        ");
        $stmt->execute([
            ':id_alumno' => $id_alumno,
            ':id_materia' => $id_materia
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtiene inasistencias de un alumno en una materia específica en un período (mes/año)
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @param int $id_materia - ID de la materia
 * @param int $mes - Mes (1-12)
 * @param int $anio - Año (ej: 2026)
 * @return int - Número de inasistencias en ese período
 */
function obtenerInasistenciasEnPeriodo($pdo, $id_alumno, $id_materia, $mes, $anio) {
    try {
        $likeMes = sprintf("%04d-%02d%%", $anio, $mes);
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM asistencia
            WHERE id_alumno = :id_alumno 
              AND id_materia = :id_materia
              AND estado = 'Ausente'
              AND fecha LIKE :mes
        ");
        $stmt->execute([
            ':id_alumno' => $id_alumno,
            ':id_materia' => $id_materia,
            ':mes' => $likeMes
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Obtiene todos los alumnos de un grupo con su conteo de inasistencias en todas las materias
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_grupo - ID del grupo
 * @return array - Array con estructura: [['id_alumno' => X, 'nombre' => Y, 'total_inasistencias' => Z], ...]
 */
function obtenerInasistenciasGrupo($pdo, $id_grupo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id_alumno,
                a.matricula,
                a.nombre,
                a.apellidos,
                a.numero_lista,
                COUNT(CASE WHEN asi.estado = 'Ausente' THEN 1 END) as total_inasistencias
            FROM alumno a
            LEFT JOIN asistencia asi ON a.id_alumno = asi.id_alumno
            WHERE a.id_grupo = :id_grupo
            GROUP BY a.id_alumno, a.matricula, a.nombre, a.apellidos, a.numero_lista
            ORDER BY a.numero_lista ASC
        ");
        $stmt->execute([':id_grupo' => $id_grupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

?>
