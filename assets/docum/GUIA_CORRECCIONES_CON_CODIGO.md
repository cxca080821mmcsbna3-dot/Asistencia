# 🔧 GUÍA DE CORRECCIONES CON CÓDIGO

**Documento:** Soluciones técnicas para cada error  
**Formato:** Código listo para copiar y pegar  
**Fecha:** 29 de enero de 2026

---

## CORRECCIÓN #1: Error de ruta en index.php

**Archivo:** `index.php`  
**Línea:** 49  
**Severidad:** 🔴 CRÍTICO

### Código Actual (INCORRECTO)
```php
header("Location: alumno/index.php");
```

### Código Corregido
```php
header("Location: ALUMNO/index.php");
```

### Explicación
El directorio se llama `ALUMNO` (mayúsculas), pero el código intenta acceder a `alumno` (minúsculas). En servidores Linux/Unix esto causa un error 404. Los alumnos no pueden loguearse.

---

## CORRECCIÓN #2: Error de nombre de archivo en ALUMNO/index.php

**Archivo:** `ALUMNO/index.php`  
**Línea:** 27  
**Severidad:** 🔴 CRÍTICO

### Código Actual (INCORRECTO)
```php
<a href="Perfil.php">Perfil</a>
```

### Código Corregido
```php
<a href="perfil.php">Perfil</a>
```

### Explicación
El archivo se llama `perfil.php` (minúsculas), pero el HTML intenta acceder a `Perfil.php` (mayúsculas). En Linux/Unix, esto causa un error 404. El botón "Perfil" no funciona.

---

## CORRECCIÓN #3: Validación de Sesión en detalleInasistencias.php

**Archivo:** `administrador/detalleInasistencias.php`  
**Línea:** 1-10  
**Severidad:** 🔴 CRÍTICO

### Código Actual (INCORRECTO)
```php
<?php
/**
 * ARCHIVO: detalleInasistencias.php
 * ...
 */

session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";

// --- Validaciones ---
if (!isset($_GET['idAlumno']) || !isset($_GET['idMateria'])) {
    header("Location: materias.php");
    exit();
}
```

### Código Corregido
```php
<?php
/**
 * ARCHIVO: detalleInasistencias.php
 * ...
 */

session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";

// 🔐 NUEVO: Validar que es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// --- Validaciones ---
if (!isset($_GET['idAlumno']) || !isset($_GET['idMateria'])) {
    header("Location: materias.php");
    exit();
}
```

### Explicación
La página debería estar protegida. Sin esta validación, cualquiera puede acceder a datos confidenciales de cualquier alumno.

---

## CORRECCIÓN #4: Validación de IDs en detalleInasistencias.php

**Archivo:** `administrador/detalleInasistencias.php`  
**Línea:** 14-20  
**Severidad:** 🔴 CRÍTICO

### Código Actual (INCORRECTO)
```php
$idAlumno = intval($_GET['idAlumno']);
$idMateria = intval($_GET['idMateria']);

// --- Obtener datos del alumno ---
try {
    $stmt = $pdo->prepare("
        SELECT a.id_alumno, a.nombre, a.apellidos, a.matricula, a.numero_lista,
               g.nombre AS nombre_grupo
        FROM alumno a
        LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
        WHERE a.id_alumno = :idAlumno
        LIMIT 1
    ");
```

### Código Corregido
```php
$idAlumno = intval($_GET['idAlumno'] ?? 0);
$idMateria = intval($_GET['idMateria'] ?? 0);

// 🔐 NUEVO: Validar que los IDs sean válidos
if ($idAlumno <= 0 || $idMateria <= 0) {
    die("❌ Parámetros inválidos");
}

// --- Validar que el alumno existe ---
$stmtValida = $pdo->prepare("SELECT id_alumno FROM alumno WHERE id_alumno = ?");
$stmtValida->execute([$idAlumno]);
if (!$stmtValida->fetch()) {
    die("❌ Alumno no encontrado");
}

// --- Validar que la materia existe ---
$stmtValida = $pdo->prepare("SELECT id_materia FROM materias WHERE id_materia = ?");
$stmtValida->execute([$idMateria]);
if (!$stmtValida->fetch()) {
    die("❌ Materia no encontrada");
}

// --- Obtener datos del alumno ---
try {
    $stmt = $pdo->prepare("
        SELECT a.id_alumno, a.nombre, a.apellidos, a.matricula, a.numero_lista,
               g.nombre AS nombre_grupo
        FROM alumno a
        LEFT JOIN grupo g ON a.id_grupo = g.idGrupo
        WHERE a.id_alumno = :idAlumno
        LIMIT 1
    ");
```

### Explicación
Sin validación, un atacante puede pasar cualquier ID y ver datos de cualquier alumno. Esta validación asegura que los IDs existen antes de usarlos.

---

## CORRECCIÓN #5: Actualizar obtenerResumenInasistenciasPorMateria()

**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php`  
**Línea:** 60-92  
**Severidad:** 🔴 CRÍTICO

### Código Actual (INCORRECTO)
```php
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
```

### Código Corregido
```php
function obtenerResumenInasistenciasPorMateria($pdo, $id_alumno) {
    try {
        // Paso 1: Obtener el grupo del alumno
        $stmtGrupo = $pdo->prepare("
            SELECT id_grupo FROM alumno WHERE id_alumno = :id_alumno
        ");
        $stmtGrupo->execute([':id_alumno' => $id_alumno]);
        $alumnoData = $stmtGrupo->fetch(PDO::FETCH_ASSOC);
        
        if (!$alumnoData) {
            return [];
        }
        
        $id_grupo = $alumnoData['id_grupo'];

        // Paso 2: Obtener TODAS las materias del grupo (no solo las con registros)
        $stmt = $pdo->prepare("
            SELECT 
                m.id_materia,
                m.nombre,
                COUNT(CASE WHEN a.estado = 'Ausente' THEN 1 END) as inasistencias,
                COUNT(CASE WHEN a.estado = 'Retardo' THEN 1 END) as retardos,
                COUNT(CASE WHEN a.estado = 'Justificante' THEN 1 END) as justificantes,
                COUNT(a.id_asistencia) as total_registros
            FROM materias m
            JOIN grupo_materia gm ON m.id_materia = gm.id_materia
            LEFT JOIN asistencia a ON m.id_materia = a.id_materia 
                                   AND a.id_alumno = :id_alumno
            WHERE gm.id_grupo = :id_grupo
            GROUP BY m.id_materia, m.nombre
            ORDER BY m.nombre ASC
        ");
        
        $stmt->execute([
            ':id_alumno' => $id_alumno,
            ':id_grupo' => $id_grupo
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en obtenerResumenInasistenciasPorMateria: " . $e->getMessage());
        return [];
    }
}
```

### Explicación
La función anterior solo mostraba materias donde el alumno tiene registros. Ahora usa LEFT JOIN para incluir TODAS las materias del grupo, mostrando 0 inasistencias si no hay registros.

---

## CORRECCIÓN #6: Simplificar consulta en detalleInasistencias.php

**Archivo:** `administrador/detalleInasistencias.php`  
**Línea:** 59-68  
**Severidad:** 🟠 MEDIO

### Código Actual (INEFICIENTE)
```php
$stmt = $pdo->prepare("
    SELECT fecha, estado,
           COUNT(*) OVER (PARTITION BY CASE WHEN estado = 'Ausente' THEN 1 ELSE 0 END) as total_ausentes,
           COUNT(*) OVER (PARTITION BY CASE WHEN estado = 'Retardo' THEN 1 ELSE 0 END) as total_retardos,
           COUNT(*) OVER (PARTITION BY CASE WHEN estado = 'Justificante' THEN 1 ELSE 0 END) as total_justificantes
    FROM asistencia
    WHERE id_alumno = :idAlumno AND id_materia = :idMateria
    ORDER BY fecha DESC
");
$stmt->execute([':idAlumno' => $idAlumno, ':idMateria' => $idMateria]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Código Corregido
```php
$stmt = $pdo->prepare("
    SELECT fecha, estado
    FROM asistencia
    WHERE id_alumno = :idAlumno AND id_materia = :idMateria
    ORDER BY fecha DESC
");
$stmt->execute([':idAlumno' => $idAlumno, ':idMateria' => $idMateria]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Explicación
Las columnas `total_ausentes`, `total_retardos`, `total_justificantes` nunca se usan. El PHP después calcula los totales manualmente. Esta versión simplificada es más rápida.

---

## CORRECCIÓN #7: Corregir métrica de materias

**Archivo:** `administrador/detalleInasistencias.php`  
**Línea:** 98-107  
**Severidad:** 🟠 MEDIO

### Código Actual (INCORRECTO)
```php
// 📊 NUEVO: Contar materias con inasistencias (ausencias, no retardos ni justificantes)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT id_materia) as total_materias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado = 'Ausente'
");
$stmt->execute([':idAlumno' => $idAlumno]);
$totalMaterias = intval($stmt->fetchColumn() ?? 0);

// 📊 NUEVO: Contar días únicos con inasistencias (ausencias)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT fecha) as total_dias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado = 'Ausente'
");
$stmt->execute([':idAlumno' => $idAlumno]);
$totalDias = intval($stmt->fetchColumn() ?? 0);
```

### Código Corregido
```php
// 📊 NUEVO: Contar materias con INASISTENCIAS (todas: Ausente, Retardo, Justificante)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT id_materia) as total_materias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado IN ('Ausente', 'Retardo', 'Justificante')
");
$stmt->execute([':idAlumno' => $idAlumno]);
$totalMaterias = intval($stmt->fetchColumn() ?? 0);

// 📊 NUEVO: Contar días únicos con INASISTENCIAS (todas)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT fecha) as total_dias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado IN ('Ausente', 'Retardo', 'Justificante')
");
$stmt->execute([':idAlumno' => $idAlumno]);
$totalDias = intval($stmt->fetchColumn() ?? 0);
```

### Explicación
Si un alumno tiene 5 Retardos en diferentes materias pero sin Ausentes, el contador debe mostrar "5 materias", no "0 materias". Ahora incluye todos los tipos de inasistencias.

---

## CORRECCIÓN #8: Proteger acceso directo a asistenciaFunciones.php

**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php`  
**Línea:** 1  
**Severidad:** 🟠 MEDIO

### Código Actual (SIN PROTECCIÓN)
```php
<?php
/**
 * ARCHIVO: asistenciaFunciones.php
 * PROPÓSITO: Funciones reutilizables para cálculos de asistencias e inasistencias
 * ...
 */

/**
 * Obtiene el total de INASISTENCIAS...
```

### Código Corregido
```php
<?php
/**
 * ARCHIVO: asistenciaFunciones.php
 * PROPÓSITO: Funciones reutilizables para cálculos de asistencias e inasistencias
 * ...
 */

// 🔐 Proteger acceso directo
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    http_response_code(403);
    die("❌ Acceso denegado");
}

/**
 * Obtiene el total de INASISTENCIAS...
```

### Explicación
Este archivo no debe ser accedido directamente desde la URL. Ahora lo protege automáticamente.

---

## CORRECCIÓN #9: Validar entrada mes y año en listaAlumnos.php

**Archivo:** `administrador/listaAlumnos.php`  
**Línea:** 14-19  
**Severidad:** 🟠 MEDIO

### Código Actual (SIN VALIDACIÓN)
```php
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
if ($mes < 1 || $mes > 12) $mes = intval(date('m'));
$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
```

### Código Corregido
```php
// Obtener mes y año con validación
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Validar rangos
$mes = max(1, min(12, $mes));
$anio = max(2000, min(2100, $anio));

$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
```

### Explicación
La validación anterior solo comprobaba mes, pero no validaba rango de año. Un atacante podría pasar año negativo o número enorme. Esta versión es más robusta.

---

## CORRECCIÓN #10: Crear archivo funciones_seguridad.php

**Archivo:** `assets/sentenciasSQL/funciones_seguridad.php` (NUEVO)  
**Severidad:** ✨ MEJORA

### Código a Crear
```php
<?php
/**
 * ARCHIVO: funciones_seguridad.php
 * PROPÓSITO: Funciones de validación y seguridad reutilizables
 * CREADO: 29 de enero de 2026
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

?>
```

### Explicación
Este archivo centraliza todas las funciones de validación y seguridad. Permite reutilizar código en todos los archivos.

---

## 📋 RESUMEN DE CAMBIOS

| Corrección | Archivo | Línea | Prioridad | Tiempo |
|---|---|---|---|---|
| #1 | index.php | 49 | 🔴 CRÍTICO | 1 min |
| #2 | ALUMNO/index.php | 27 | 🔴 CRÍTICO | 1 min |
| #3 | administrador/detalleInasistencias.php | 1-15 | 🔴 CRÍTICO | 5 min |
| #4 | administrador/detalleInasistencias.php | 14-20 | 🔴 CRÍTICO | 10 min |
| #5 | assets/sentenciasSQL/asistenciaFunciones.php | 60-92 | 🔴 CRÍTICO | 15 min |
| #6 | administrador/detalleInasistencias.php | 59-68 | 🟠 MEDIO | 5 min |
| #7 | administrador/detalleInasistencias.php | 98-107 | 🟠 MEDIO | 5 min |
| #8 | assets/sentenciasSQL/asistenciaFunciones.php | 1 | 🟠 MEDIO | 5 min |
| #9 | administrador/listaAlumnos.php | 14-19 | 🟠 MEDIO | 5 min |
| #10 | assets/sentenciasSQL/funciones_seguridad.php | NUEVO | ✨ MEJORA | 20 min |

**Tiempo Total de Corrección:** ~77 minutos (1 hora 17 minutos)

---

## ✅ CHECKLIST

- [ ] Corrección #1 implementada
- [ ] Corrección #2 implementada
- [ ] Corrección #3 implementada
- [ ] Corrección #4 implementada
- [ ] Corrección #5 implementada
- [ ] Corrección #6 implementada
- [ ] Corrección #7 implementada
- [ ] Corrección #8 implementada
- [ ] Corrección #9 implementada
- [ ] Corrección #10 implementada
- [ ] Pruebas de login (alumno, admin, docente)
- [ ] Pruebas de acceso a perfil
- [ ] Pruebas de acceso a detalleInasistencias
- [ ] Pruebas con datos faltantes

---

**Documento generado:** 29 de enero de 2026
