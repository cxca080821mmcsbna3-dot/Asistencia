# ✅ CHECKLIST INTERACTIVO DE CORRECCIONES

**Fecha:** 29 de enero de 2026  
**Objetivo:** Rastrear el progreso de implementación de todas las correcciones  
**Duración Estimada:** 2 horas

---

## 🔴 FASE 1: CORRECCIONES CRÍTICAS (25 minutos)

### [ ] PASO 1.1: Ruta de login - index.php (1 min)
**Archivo:** `index.php` línea 49  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Abre el archivo `index.php`
- [ ] Busca: `header("Location: alumno/index.php");`
- [ ] Reemplaza con: `header("Location: ALUMNO/index.php");`
- [ ] Guarda el archivo

**Verificación:**
- [ ] Prueba login de alumno
- [ ] Deberías entrar correctamente

**Tiempo:** 1 minuto  
**Crítico:** 🔴 SÍ

---

### [ ] PASO 1.2: Nombre de archivo - ALUMNO/index.php (1 min)
**Archivo:** `ALUMNO/index.php` línea 27  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Abre el archivo `ALUMNO/index.php`
- [ ] Busca: `<a href="Perfil.php">Perfil</a>`
- [ ] Reemplaza con: `<a href="perfil.php">Perfil</a>`
- [ ] Guarda el archivo

**Verificación:**
- [ ] Logúeate como alumno
- [ ] Haz clic en "Perfil"
- [ ] Debería cargar sin error 404

**Tiempo:** 1 minuto  
**Crítico:** 🔴 SÍ

---

### [ ] PASO 1.3: Validación de sesión admin (3 min)
**Archivo:** `administrador/detalleInasistencias.php` línea ~10  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Abre `administrador/detalleInasistencias.php`
- [ ] Localiza línea con `session_start();` y los `require_once`
- [ ] Después de los `require_once`, agrega:

```php
// 🔐 Validar que es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
```

- [ ] Guarda el archivo

**Verificación:**
- [ ] Intenta acceder sin estar logeado como admin
- [ ] Deberías ser redirigido al login

**Tiempo:** 3 minutos  
**Crítico:** 🔴 SÍ

---

### [ ] PASO 1.4: Validación de IDs (5 min)
**Archivo:** `administrador/detalleInasistencias.php` línea ~14-20  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Localiza:
```php
$idAlumno = intval($_GET['idAlumno']);
$idMateria = intval($_GET['idMateria']);

// --- Obtener datos del alumno ---
try {
```

- [ ] Reemplaza con:
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

- [ ] Guarda el archivo

**Verificación:**
- [ ] Accede a URL con IDs inválidos
- [ ] Deberías ver mensaje de error

**Tiempo:** 5 minutos  
**Crítico:** 🔴 SÍ

---

### [ ] PASO 1.5: Actualizar función (10 min)
**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php` línea ~60-92  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Localiza la función `obtenerResumenInasistenciasPorMateria()`
- [ ] Reemplaza toda la función (ver GUIA_CORRECCIONES_CON_CODIGO.md corrección #5)
- [ ] Guarda el archivo

**Verificación:**
- [ ] Accede al perfil de un alumno
- [ ] Tabla debe mostrar TODAS las materias

**Tiempo:** 10 minutos  
**Crítico:** 🔴 SÍ

---

## 🟠 FASE 2: OPTIMIZACIONES (15 minutos)

### [ ] PASO 2.1: Simplificar consulta SQL (3 min)
**Archivo:** `administrador/detalleInasistencias.php` línea ~59  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Localiza la consulta con window functions
- [ ] Reemplaza con versión simplificada (ver GUIA_CORRECCIONES_CON_CODIGO.md)
- [ ] Guarda el archivo

**Verificación:**
- [ ] Página debe cargar más rápido

**Tiempo:** 3 minutos  
**Crítico:** 🟠 NO

---

### [ ] PASO 2.2: Corregir métricas (5 min)
**Archivo:** `administrador/detalleInasistencias.php` línea ~98-107  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Localiza las dos consultas de COUNT
- [ ] Reemplaza ambas con versión que incluye Retardo y Justificante
- [ ] Guarda el archivo

**Verificación:**
- [ ] Métricas ahora incluyen todos los tipos de inasistencias

**Tiempo:** 5 minutos  
**Crítico:** 🟠 NO

---

### [ ] PASO 2.3: Proteger archivo (3 min)
**Archivo:** `assets/sentenciasSQL/asistenciaFunciones.php` línea 1  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] En la línea 1, después de `<?php`, agrega:
```php
// 🔐 Proteger acceso directo
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    http_response_code(403);
    die("❌ Acceso denegado");
}
```
- [ ] Guarda el archivo

**Verificación:**
- [ ] Intenta acceder a la URL del archivo
- [ ] Deberías ver "❌ Acceso denegado"

**Tiempo:** 3 minutos  
**Crítico:** 🟠 NO

---

### [ ] PASO 2.4: Mejorar validación (3 min)
**Archivo:** `administrador/listaAlumnos.php` línea ~14-19  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Localiza la sección de mes/año
- [ ] Reemplaza con versión mejorada (ver GUIA_CORRECCIONES_CON_CODIGO.md)
- [ ] Guarda el archivo

**Verificación:**
- [ ] Página debe funcionar con mes/año inválidos

**Tiempo:** 3 minutos  
**Crítico:** 🟠 NO

---

## ✨ FASE 3: MEJORAS (20 minutos)

### [ ] PASO 3.1: Crear archivo seguridad (15 min)
**Archivo:** `assets/sentenciasSQL/funciones_seguridad.php` (NUEVO)  
**Estado:** ⏳ Pendiente

**Tarea:**
- [ ] Crea nuevo archivo: `assets/sentenciasSQL/funciones_seguridad.php`
- [ ] Copia contenido de GUIA_CORRECCIONES_CON_CODIGO.md sección CORRECCIÓN #10
- [ ] Guarda el archivo

**Verificación:**
- [ ] Archivo creado correctamente

**Tiempo:** 15 minutos  
**Crítico:** ✨ NO (MEJORA)

---

### [ ] PASO 3.2: Usar nuevas funciones (5 min - OPCIONAL)
**Archivo:** Múltiples (OPCIONAL)  
**Estado:** ⏳ Pendiente (OPCIONAL)

**Tarea:**
- [ ] En `administrador/detalleInasistencias.php`
- [ ] Agrega: `require_once __DIR__ . "/../assets/sentenciasSQL/funciones_seguridad.php";`
- [ ] Reemplaza validación de sesión con: `requerirAdmin();`
- [ ] Guarda el archivo

**Verificación:**
- [ ] Funciona igual que antes

**Tiempo:** 5 minutos  
**Crítico:** ✨ NO (OPCIONAL)

---

## 🧪 FASE 4: PRUEBAS (30 minutos)

### TEST 1: Login de Alumno ✅
**Pasos:**
- [ ] Abre `http://localhost/Asistencia/index.php`
- [ ] Logúeate como alumno (tu matrícula y CURP)
- [ ] Deberías entrar sin error

**Resultado:** ⏳ Pendiente  
**Tiempo:** 5 minutos

---

### TEST 2: Perfil del Alumno ✅
**Pasos:**
- [ ] Habiendo logeado, haz clic en "Perfil"
- [ ] Deberías ver tu perfil sin error 404
- [ ] Tabla debe mostrar TODAS tus materias
- [ ] Debe haber un badge con total de inasistencias

**Resultado:** ⏳ Pendiente  
**Tiempo:** 5 minutos

---

### TEST 3: Login de Admin ✅
**Pasos:**
- [ ] Cierra sesión (alumno)
- [ ] Logúeate como administrador
- [ ] Deberías entrar al menú de grupos

**Resultado:** ⏳ Pendiente  
**Tiempo:** 3 minutos

---

### TEST 4: Detalles de Inasistencias ✅
**Pasos:**
- [ ] Como admin, ve a una lista de alumnos
- [ ] Haz clic en el nombre de un alumno
- [ ] Deberías ver la página de detalles sin errores
- [ ] Tabla de resumen debe mostrar TODAS las materias
- [ ] Las métricas deben incluir Retardos y Justificantes

**Resultado:** ⏳ Pendiente  
**Tiempo:** 5 minutos

---

### TEST 5: Seguridad - Acceso sin Autorizar ✅
**Pasos:**
- [ ] Cierra sesión completamente
- [ ] Intenta acceder a: `administrador/detalleInasistencias.php?idAlumno=1&idMateria=1`
- [ ] Deberías ser redirigido al login (NO deberías ver la página)

**Resultado:** ⏳ Pendiente  
**Tiempo:** 3 minutos

---

### TEST 6: Seguridad - Acceso Directo a Archivo ✅
**Pasos:**
- [ ] Intenta acceder a: `assets/sentenciasSQL/asistenciaFunciones.php`
- [ ] Deberías ver: "❌ Acceso denegado"

**Resultado:** ⏳ Pendiente  
**Tiempo:** 3 minutos

---

### TEST 7: Validación de IDs ✅
**Pasos:**
- [ ] Logúeate como admin
- [ ] Accede a: `administrador/detalleInasistencias.php?idAlumno=99999&idMateria=1`
- [ ] Deberías ver: "❌ Alumno no encontrado"
- [ ] Accede a: `administrador/detalleInasistencias.php?idAlumno=1&idMateria=99999`
- [ ] Deberías ver: "❌ Materia no encontrada"

**Resultado:** ⏳ Pendiente  
**Tiempo:** 3 minutos

---

## 📊 RESUMEN DE PROGRESO

### Fase 1: Crítica
```
Pasos completados: [     ] 0/5
Progreso: 0%
```
- [ ] Paso 1.1: ⏳
- [ ] Paso 1.2: ⏳
- [ ] Paso 1.3: ⏳
- [ ] Paso 1.4: ⏳
- [ ] Paso 1.5: ⏳

### Fase 2: Optimización
```
Pasos completados: [     ] 0/4
Progreso: 0%
```
- [ ] Paso 2.1: ⏳
- [ ] Paso 2.2: ⏳
- [ ] Paso 2.3: ⏳
- [ ] Paso 2.4: ⏳

### Fase 3: Mejoras
```
Pasos completados: [     ] 0/2
Progreso: 0%
```
- [ ] Paso 3.1: ⏳
- [ ] Paso 3.2: ⏳ (OPCIONAL)

### Fase 4: Pruebas
```
Pruebas pasadas: [     ] 0/7
Progreso: 0%
```
- [ ] Test 1: ⏳
- [ ] Test 2: ⏳
- [ ] Test 3: ⏳
- [ ] Test 4: ⏳
- [ ] Test 5: ⏳
- [ ] Test 6: ⏳
- [ ] Test 7: ⏳

---

## ⏱️ TIEMPO TOTAL

| Fase | Pasos | Tiempo |
|---|---|---|
| 1: Crítica | 5 | 25 min |
| 2: Optimización | 4 | 15 min |
| 3: Mejoras | 2 | 20 min |
| 4: Pruebas | 7 | 30 min |
| **TOTAL** | **18** | **90 min** |

**Tiempo Estimado Total:** 1.5 horas

---

## 🎯 CHECKLIST FINAL

### Antes de Empezar
- [ ] Hice un backup del proyecto
- [ ] Tengo acceso a todos los archivos
- [ ] Entiendo cada corrección antes de implementarla

### Implementación Completada
- [ ] Todos los pasos de Fase 1 completados
- [ ] Todos los pasos de Fase 2 completados
- [ ] Todos los pasos de Fase 3 completados (mínimo el 3.1)

### Pruebas Completadas
- [ ] Test 1 ✅
- [ ] Test 2 ✅
- [ ] Test 3 ✅
- [ ] Test 4 ✅
- [ ] Test 5 ✅
- [ ] Test 6 ✅
- [ ] Test 7 ✅

### Finalización
- [ ] Todos los tests pasaron ✅
- [ ] Sistema está listo para producción
- [ ] Documenté los cambios realizados
- [ ] Informé al equipo sobre las correcciones

---

## 📝 NOTAS PERSONALES

(Espacio para escribir notas mientras implementas)

```
Hora de inicio: ___________
Problemas encontrados: 




Soluciones aplicadas: 




Hora de fin: ___________
Tiempo total: ___________
```

---

## 🚨 EN CASO DE PROBLEMAS

Si algo sale mal:

1. **Error en login:** Ver sección "Si algo sale mal" en PLAN_IMPLEMENTACION_PASO_A_PASO.md
2. **Error en perfil:** Verifica cambios en pasos 1.1 y 1.2
3. **Errores PHP:** Busca el error en ANALISIS_ERRORES_Y_MEJORAS.md
4. **Necesitas ayuda:** Copia el código exacto de GUIA_CORRECCIONES_CON_CODIGO.md

---

## ✅ AL COMPLETAR TODO

🎉 **¡Felicidades!**

Tu sistema de asistencia ahora:
- ✅ Es funcional y completo
- ✅ Es seguro contra ataques
- ✅ Muestra datos correctos
- ✅ Tiene mejor rendimiento
- ✅ Está listo para producción

**Próximos pasos:**
- [ ] Monitorear en producción
- [ ] Recopilar feedback de usuarios
- [ ] Implementar mejoras adicionales

---

**Documento generado:** 29 de enero de 2026  
**Versión:** 1.0
