<?php
/**
 * ARCHIVO: funciones_seguridad.php
 * PROPÓSITO: Funciones de validación y seguridad reutilizables
 * CREADO: 29 de enero de 2026
 * CORRECCIÓN: #10
 */

// 🔐 Proteger acceso directo
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    http_response_code(403);
    die("❌ Acceso denegado");
}

/**
 * Validar que el usuario actual es un administrador
 * Si no lo es, redirige al login y termina la ejecución
 */
function requerirAdmin() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * Validar que el usuario actual es un alumno
 */
function requerirAlumno() {
    if (!isset($_SESSION['ALUMNO'])) {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * Validar que el usuario actual es un docente
 */
function requerirDocente() {
    if (!isset($_SESSION['DOCENTE'])) {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * Validar que un ID es un número entero positivo
 * @param int $id - El ID a validar
 * @param int $minimo - Mínimo permitido (default 1)
 * @return bool - true si es válido, false si no
 */
function esIdValido($id, $minimo = 1) {
    return is_int($id) && $id >= $minimo;
}

/**
 * Obtener y validar un ID de un array (GET/POST)
 * @param array $array - $_GET o $_POST
 * @param string $nombre - Nombre del parámetro
 * @param int $minimo - Mínimo permitido (default 1)
 * @return int|null - El ID validado o null si es inválido
 */
function obtenerIdValidado($array, $nombre, $minimo = 1) {
    if (!isset($array[$nombre])) {
        return null;
    }
    
    $id = intval($array[$nombre] ?? 0);
    
    if (esIdValido($id, $minimo)) {
        return $id;
    }
    
    return null;
}

/**
 * Validar mes (1-12) y año (2000-2100)
 * @param int $mes - Mes a validar
 * @param int $anio - Año a validar
 * @return array - [$mes_validado, $anio_validado]
 */
function validarMesYAnio($mes, $anio) {
    $mes = max(1, min(12, intval($mes)));
    $anio = max(2000, min(2100, intval($anio)));
    
    return [$mes, $anio];
}

/**
 * Validar que un registro existe en la BD
 * @param PDO $pdo - Conexión a BD
 * @param string $tabla - Nombre de la tabla
 * @param string $columna - Nombre de la columna (usualmente id)
 * @param int $id - ID a verificar
 * @return bool - true si existe, false si no
 */
function registroExiste($pdo, $tabla, $columna, $id) {
    if ($id <= 0) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM {$tabla} WHERE {$columna} = ? LIMIT 1");
        $stmt->execute([$id]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        error_log("Error en registroExiste: " . $e->getMessage());
        return false;
    }
}

/**
 * Validar que el alumno pertenece a un grupo específico
 * @param PDO $pdo - Conexión a BD
 * @param int $id_alumno - ID del alumno
 * @param int $id_grupo - ID del grupo
 * @return bool - true si pertenece, false si no
 */
function alumnoPerteneceeGrupo($pdo, $id_alumno, $id_grupo) {
    try {
        $stmt = $pdo->prepare("
            SELECT id_alumno FROM alumno 
            WHERE id_alumno = ? AND id_grupo = ?
            LIMIT 1
        ");
        $stmt->execute([$id_alumno, $id_grupo]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        error_log("Error en alumnoPerteneceeGrupo: " . $e->getMessage());
        return false;
    }
}

/**
 * Sistema de mensajes para mostrar en pantalla sin die()
 * Genera HTML para mostrar mensajes de error, éxito, advertencia
 * @param string $tipo - 'error', 'exito', 'advertencia', 'info'
 * @param string $mensaje - El mensaje a mostrar
 * @param string $detalles - (Opcional) Detalles adicionales
 * @return string - HTML del mensaje
 */
function generarMensaje($tipo, $mensaje, $detalles = '') {
    $iconos = [
        'error' => '❌',
        'exito' => '✅',
        'advertencia' => '⚠️',
        'info' => 'ℹ️'
    ];
    
    $clases = [
        'error' => 'mensaje-error',
        'exito' => 'mensaje-exito',
        'advertencia' => 'mensaje-advertencia',
        'info' => 'mensaje-info'
    ];
    
    $icono = $iconos[$tipo] ?? '🔔';
    $clase = $clases[$tipo] ?? 'mensaje-info';
    
    $html = '<div class="mensaje-contenedor ' . $clase . '">';
    $html .= '<div class="mensaje-header">';
    $html .= '<span class="mensaje-icono">' . $icono . '</span>';
    $html .= '<span class="mensaje-texto">' . htmlspecialchars($mensaje) . '</span>';
    $html .= '<button class="mensaje-cerrar" onclick="this.parentElement.parentElement.remove();">&times;</button>';
    $html .= '</div>';
    
    if (!empty($detalles)) {
        $html .= '<div class="mensaje-detalles">' . htmlspecialchars($detalles) . '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Mostrar mensaje de error sin interrumpir la página
 * @param string $mensaje - El mensaje de error
 * @param string $detalles - (Opcional) Detalles adicionales
 */
function mostrarMensajeError($mensaje, $detalles = '') {
    echo generarMensaje('error', $mensaje, $detalles);
}

/**
 * Mostrar mensaje de éxito sin interrumpir la página
 * @param string $mensaje - El mensaje de éxito
 */
function mostrarMensajeExito($mensaje) {
    echo generarMensaje('exito', $mensaje);
}

/**
 * Mostrar mensaje de advertencia sin interrumpir la página
 * @param string $mensaje - El mensaje de advertencia
 */
function mostrarMensajeAdvertencia($mensaje) {
    echo generarMensaje('advertencia', $mensaje);
}

/**
 * Mostrar mensaje de información
 * @param string $mensaje - El mensaje de información
 */
function mostrarMensajeInfo($mensaje) {
    echo generarMensaje('info', $mensaje);
}

/**
 * Obtener HTML de estilos CSS para mensajes
 * Usar al principio de <head>
 */
function estilosMensajes() {
    return '<style>
.mensaje-contenedor {
    margin: 15px auto;
    padding: 15px;
    border-radius: 6px;
    max-width: 600px;
    border-left: 4px solid #ddd;
    animation: slideIn 0.3s ease-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.mensaje-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mensaje-icono {
    font-size: 20px;
    min-width: 25px;
}

.mensaje-texto {
    flex-grow: 1;
    font-weight: 500;
}

.mensaje-cerrar {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: inherit;
    padding: 0;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.mensaje-cerrar:hover {
    opacity: 1;
}

.mensaje-detalles {
    margin-top: 10px;
    padding: 10px;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
    font-size: 14px;
    font-family: monospace;
}

.mensaje-error {
    background-color: #fff5f5;
    color: #c53030;
    border-left-color: #c53030;
}

.mensaje-exito {
    background-color: #f0fdf4;
    color: #15803d;
    border-left-color: #15803d;
}

.mensaje-advertencia {
    background-color: #fffbeb;
    color: #92400e;
    border-left-color: #f59e0b;
}

.mensaje-info {
    background-color: #f0f9ff;
    color: #0c4a6e;
    border-left-color: #0284c7;
}

@keyframes slideIn {
    from {
        transform: translateX(-20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 600px) {
    .mensaje-contenedor {
        margin: 10px;
    }
}
</style>';
}

?>
