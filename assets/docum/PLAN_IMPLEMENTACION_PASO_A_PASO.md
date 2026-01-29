# 📋 PLAN DE IMPLEMENTACIÓN DE CORRECCIONES

**Fecha:** 29 de enero de 2026  
**Objetivo:** Corregir todos los errores del sistema  
**Duración estimada:** 2 horas  
**Riesgo:** Bajo (cambios localizados)

---

## FASE 1: CORRECCIONES CRÍTICAS (25 minutos)

### Paso 1.1: Corregir ruta del login (1 minuto)

**Archivo:** `index.php`

1. Abre el archivo `index.php`
2. Busca la línea: `header("Location: alumno/index.php");`
3. Reemplaza con: `header("Location: ALUMNO/index.php");`
4. Guarda

**Verificación:**
- Intenta loguearte como alumno
- Deberías entrar correctamente a ALUMNO/index.php

---

### Paso 1.2: Corregir nombre de archivo del perfil (1 minuto)

**Archivo:** `ALUMNO/index.php`

1. Abre el archivo `ALUMNO/index.php`
2. Busca la línea: `<a href="Perfil.php">Perfil</a>`
3. Reemplaza con: `<a href="perfil.php">Perfil</a>`
4. Guarda

**Verificación:**
- Luego de loguearte como alumno, haz clic en "Perfil"
- Deberías ver tu perfil sin error 404

---

### Paso 1.3: Agregar validación de sesión admin (3 minutos)

**Archivo:** `administrador/detalleInasistencias.php`

1. Abre el archivo
2. Localiza las líneas iniciales (después de `session_start()` y los `require_once`)
3. Agrega este bloque después de los `require_once`:

```php
// 🔐 Validar que es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
```

4. Guarda

**Verificación:**
- Intenta acceder a `administrador/detalleInasistencias.php?idAlumno=1&idMateria=1` sin estar logeado como admin
- Deberías ser redirigido al login

---

### Paso 1.4: Validar IDs en detalleInasistencias.php (5 minutos)

**Archivo:** `administrador/detalleInasistencias.php`

1. Localiza estas líneas:
```php
$idAlumno = intval($_GET['idAlumno']);
$idMateria = intval($_GET['idMateria']);

// --- Obtener datos del alumno ---
try {
```

2. Reemplaza con:
```php
$idAlumno = intval($_GET['idAlumno'] ?? 0);
$idMateria = intval($_GET['idMateria'] ?? 0);

// 🔐 Validar que los IDs sean válidos
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
```

3. Guarda

**Verificación:**
- Accede a `administrador/detalleInasistencias.php?idAlumno=99999&idMateria=1`
- Deberías ver el mensaje "❌ Alumno no encontrado"

---

### Paso 1.5: Actualizar función obtenerResumenInasistenciasPorMateria() (10 minutos)

**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php`

1. Localiza la función (empieza en línea ~60)
2. Reemplaza toda la función con:

```php
/**
 * Obtiene un resumen completo de inasistencias por materia para un alumno
 * INCLUYE TODAS las materias del grupo, no solo las que tienen registros
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id_alumno - ID del alumno
 * @return array - Array con estructura: [['id_materia' => X, 'nombre' => Y, 'inasistencias' => Z], ...]
 */
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

3. Guarda

**Verificación:**
- Accede al perfil de un alumno
- La tabla de inasistencias debería mostrar TODAS las materias, no solo las con registros

---

## FASE 2: OPTIMIZACIONES (15 minutos)

### Paso 2.1: Simplificar consulta en detalleInasistencias.php (3 minutos)

**Archivo:** `administrador/detalleInasistencias.php`

1. Localiza la consulta con window functions (línea ~59):
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
```

2. Reemplaza con:
```php
$stmt = $pdo->prepare("
    SELECT fecha, estado
    FROM asistencia
    WHERE id_alumno = :idAlumno AND id_materia = :idMateria
    ORDER BY fecha DESC
");
```

3. Guarda

---

### Paso 2.2: Corregir métricas de materias (5 minutos)

**Archivo:** `administrador/detalleInasistencias.php`

1. Localiza las dos consultas (líneas ~98-107):
```php
// 📊 NUEVO: Contar materias con inasistencias (ausencias, no retardos ni justificantes)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT id_materia) as total_materias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado = 'Ausente'
");

// 📊 NUEVO: Contar días únicos con inasistencias (ausencias)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT fecha) as total_dias
    FROM asistencia
    WHERE id_alumno = :idAlumno AND estado = 'Ausente'
");
```

2. Reemplaza con:
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

3. Guarda

---

### Paso 2.3: Proteger acceso a asistenciaFunciones.php (3 minutos)

**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php`

1. En la línea 1, después del `<?php`, agrega:
```php
// 🔐 Proteger acceso directo
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    http_response_code(403);
    die("❌ Acceso denegado");
}
```

2. Guarda

**Verificación:**
- Intenta acceder a `assets/sentenciasSQL/asistenciaFunciones.php` en el navegador
- Deberías ver "❌ Acceso denegado"

---

### Paso 2.4: Mejorar validación de mes/año (3 minutos)

**Archivo:** `administrador/listaAlumnos.php`

1. Localiza (línea ~14):
```php
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
if ($mes < 1 || $mes > 12) $mes = intval(date('m'));
$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
```

2. Reemplaza con:
```php
// Obtener mes y año con validación completa
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Validar rangos
$mes = max(1, min(12, $mes));
$anio = max(2000, min(2100, $anio));

$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
```

3. Guarda

---

## FASE 3: MEJORAS (20 minutos)

### Paso 3.1: Crear archivo de seguridad (15 minutos)

**Archivo:** `assets/sentenciasSQL/funciones_seguridad.php` (NUEVO)

1. Crea un nuevo archivo: `assets/sentenciasSQL/funciones_seguridad.php`
2. Copia el contenido completo desde el documento "GUIA_CORRECCIONES_CON_CODIGO.md" (sección CORRECCIÓN #10)
3. Guarda

---

### Paso 3.2: (OPCIONAL) Actualizar archivos para usar funciones_seguridad.php (5 minutos)

Este paso es opcional pero recomendado.

**En administrador/detalleInasistencias.php:**

Agrega en el `require_once` del archivo:
```php
require_once __DIR__ . "/../assets/sentenciasSQL/funciones_seguridad.php";
```

Y reemplaza la validación de sesión con:
```php
// 🔐 Validar que es administrador
requerirAdmin();
```

---

## FASE 4: PRUEBAS (30 minutos)

### Prueba 1: Login de Alumno
- [ ] Abre `http://localhost/Asistencia/index.php`
- [ ] Intenta loguearte como alumno (con tu matrícula y CURP)
- [ ] Deberías entrar sin error

### Prueba 2: Perfil del Alumno
- [ ] Habiendo logeado como alumno, haz clic en "Perfil"
- [ ] Deberías ver tu perfil sin error 404
- [ ] Debería mostrar todas tus materias (incluso las sin inasistencias)

### Prueba 3: Login de Admin
- [ ] Cierra sesión del alumno
- [ ] Logúeate como administrador
- [ ] Deberías entrar al menu de grupos

### Prueba 4: Detalles de Inasistencias
- [ ] Como administrador, ve a una lista de alumnos
- [ ] Haz clic en el nombre de un alumno
- [ ] Deberías ver la página de detalles sin errores
- [ ] La tabla de resumen debe mostrar TODAS las materias

### Prueba 5: Seguridad
- [ ] Intenta acceder directamente a `administrador/detalleInasistencias.php` sin estar logeado
- [ ] Deberías ser redirigido al login
- [ ] Intenta acceder a `assets/sentenciasSQL/asistenciaFunciones.php` en el navegador
- [ ] Deberías ver "❌ Acceso denegado"

### Prueba 6: Validación de IDs
- [ ] Intenta acceder a `administrador/detalleInasistencias.php?idAlumno=99999&idMateria=1`
- [ ] Deberías ver "❌ Alumno no encontrado"
- [ ] Intenta acceder a `administrador/detalleInasistencias.php?idAlumno=1&idMateria=99999`
- [ ] Deberías ver "❌ Materia no encontrada"

---

## ⚠️ NOTAS IMPORTANTES

### Si algo sale mal:
1. **Error en login:** Verifica que cambiaste `alumno/` a `ALUMNO/` en index.php línea 49
2. **Error en perfil:** Verifica que cambiaste `Perfil.php` a `perfil.php` en ALUMNO/index.php línea 27
3. **Acceso no autorizado:** Verifica que agregaste la validación de sesión admin
4. **Tabla vacía:** Verifica que actualizaste `obtenerResumenInasistenciasPorMateria()`

### Backup:
Antes de hacer cambios, es recomendable hacer un backup:
```bash
# En Windows PowerShell
Copy-Item -Path "c:\xampp\htdocs\Asistencia" -Destination "c:\xampp\htdocs\Asistencia_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Recurse
```

---

## ✅ CHECKLIST FINAL

### Fase 1: Crítica
- [ ] Paso 1.1 completado y verificado
- [ ] Paso 1.2 completado y verificado
- [ ] Paso 1.3 completado y verificado
- [ ] Paso 1.4 completado y verificado
- [ ] Paso 1.5 completado y verificado

### Fase 2: Optimización
- [ ] Paso 2.1 completado
- [ ] Paso 2.2 completado
- [ ] Paso 2.3 completado y verificado
- [ ] Paso 2.4 completado

### Fase 3: Mejoras
- [ ] Paso 3.1 completado
- [ ] Paso 3.2 completado (opcional)

### Fase 4: Pruebas
- [ ] Prueba 1: Login de alumno ✓
- [ ] Prueba 2: Perfil del alumno ✓
- [ ] Prueba 3: Login de admin ✓
- [ ] Prueba 4: Detalles de inasistencias ✓
- [ ] Prueba 5: Seguridad ✓
- [ ] Prueba 6: Validación de IDs ✓

### Finalización
- [ ] Todos los errores corregidos
- [ ] Todas las pruebas pasadas
- [ ] Sistema en producción

---

**Tiempo Total:** ~2 horas  
**Riesgo:** Bajo  
**Impacto:** Alto - Todas las funcionalidades principales funcionarán correctamente

**Próximos Pasos:**
1. Monitorear la aplicación en producción
2. Recopilar feedback de usuarios
3. Implementar mejoras adicionales según necesidad

---

**Documento generado:** 29 de enero de 2026  
**Autor:** Sistema de Análisis de Código
