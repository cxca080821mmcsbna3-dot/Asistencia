# 📋 DOCUMENTO DE CAMBIOS - SISTEMA DE INASISTENCIAS
**Fecha:** 20 de enero de 2026  
**Objetivo:** Implementar conteo de inasistencias por alumno visible en perfil de alumno y en administrador

---

## 📊 RESUMEN DE CAMBIOS

### ✅ ARCHIVOS CREADOS

#### 1. `assets/sentenciasSQL/asistenciaFunciones.php` (NUEVO)
**Ubicación:** `c:\xampp\htdocs\Asistencia\assets\sentenciasSQL\asistenciaFunciones.php`

**Descripción:** Archivo con 7 funciones reutilizables para cálculos de inasistencias.

**Funciones incluidas:**
- `obtenerTotalInasistencias($pdo, $id_alumno)` - Total de inasistencias en todas las materias
- `obtenerInasistenciasPorMateria($pdo, $id_alumno, $id_materia)` - Inasistencias en materia específica
- `obtenerResumenInasistenciasPorMateria($pdo, $id_alumno)` - Resumen completo por materia
- `obtenerInasistenciasGrupoMateria($pdo, $id_materia, $id_grupo)` - Para grupos completos
- `obtenerHistorialInasistencias($pdo, $id_alumno, $id_materia)` - Historial con fechas
- `obtenerInasistenciasEnPeriodo($pdo, $id_alumno, $id_materia, $mes, $anio)` - Por período
- `obtenerInasistenciasGrupo($pdo, $id_grupo)` - Todos alumnos de un grupo

**Cómo revertir:** Eliminar el archivo `asistenciaFunciones.php`

---

#### 2. `administrador/detalleInasistencias.php` (NUEVO)
**Ubicación:** `c:\xampp\htdocs\Asistencia\administrador\detalleInasistencias.php`

**Descripción:** Página de detalle que muestra:
- Información del alumno
- Materia seleccionada
- Tarjetas con estadísticas (ausencias, retardos, justificantes, presencias)
- Tabla con historial completo de asistencias/inasistencias
- Soporte para modo oscuro

**Parámetros GET necesarios:**
- `idAlumno` - ID del alumno
- `idMateria` - ID de la materia

**Se accede desde:** Haciendo clic en el nombre del alumno en `administrador/listaAlumnos.php`

**Cómo revertir:** Eliminar el archivo `detalleInasistencias.php`

---

### ✅ ARCHIVOS MODIFICADOS

#### 1. `ALUMNO/perfil.php`
**Ubicación:** `c:\xampp\htdocs\Asistencia\ALUMNO\perfil.php`

**Cambios realizados:**

a) **Línea 3** - Agregar import de funciones:
```php
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";
```

b) **Después de línea 32** - Agregar variables de inasistencias:
```php
// 📊 NUEVO: Obtener resumen de inasistencias por materia
$resumenInasistencias = obtenerResumenInasistenciasPorMateria($pdo, $idAlumno);
$totalInasistencias = obtenerTotalInasistencias($pdo, $idAlumno);
```

c) **En la sección perfil-body** - Agregar nueva sección HTML:
```php
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
```

**Cómo revertir:**
1. Eliminar línea 3 (require_once asistenciaFunciones.php)
2. Eliminar las 2 líneas de obtener datos después de línea 32
3. Eliminar la sección "NUEVO: Sección de Inasistencias" del HTML

---

#### 2. `ALUMNO/css/perfil.css`
**Ubicación:** `c:\xampp\htdocs\Asistencia\ALUMNO\css\perfil.css`

**Cambios realizados:**

Agregados estilos para la nueva sección de inasistencias (después de los estilos de modo oscuro):

```css
/* ======================================
   ESTILOS PARA INASISTENCIAS (NUEVO)
====================================== */
.inasistencias-seccion { ... }
.total-badge { ... }
.inasistencias-tabla { ... }
/* ... más estilos ... */
```

**Características de los estilos:**
- Tarjeta circular roja para badge de total
- Tabla responsiva con colores por estado
- Soporte completo para modo oscuro
- Hover effects en filas de tabla

**Cómo revertir:** 
Eliminar desde la línea que comienza con `/* ======================================` hasta el final del archivo.

---

#### 3. `administrador/listaAlumnos.php`
**Ubicación:** `c:\xampp\htdocs\Asistencia\administrador\listaAlumnos.php`

**Cambios realizados:**

a) **Línea 3** - Agregar import:
```php
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";
```

b) **Después de consultar alumnos (línea ~68)** - Agregar cálculo de inasistencias:
```php
// 📊 NUEVO: Obtener inasistencias totales por alumno en esta materia
$alumnosConInasistencias = [];
foreach ($alumnos as $alumno) {
    $inasistenciasMateria = obtenerInasistenciasPorMateria($pdo, $alumno['id_alumno'], $id_materia);
    $alumno['inasistencias'] = $inasistenciasMateria;
    $alumnosConInasistencias[] = $alumno;
}
$alumnos = $alumnosConInasistencias;
```

c) **En thead de tabla** - Agregar encabezado:
```html
<th style="background-color: #ffcccc; color: #8b0000;">⚠️ Inasist.</th>
```

d) **En tbody de tabla** - Agregar columna de inasistencias y convertir nombre a link:
```php
<td class="alumno-col">
    <a href="detalleInasistencias.php?idAlumno=<?= $al['id_alumno'] ?>&idMateria=<?= $id_materia ?>" style="text-decoration: none; color: #4b3621; font-weight: bold;">
        <?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?>
    </a>
</td>
<td style="background-color: <?= $al['inasistencias'] > 0 ? '#ffebee' : '#f0f0f0' ?>;">
    <strong style="color: <?= $al['inasistencias'] > 0 ? '#d32f2f' : '#4caf50' ?>;">
        <?= $al['inasistencias'] ?>
    </strong>
</td>
```

**Cómo revertir:**
1. Eliminar línea 3 (require_once asistenciaFunciones.php)
2. Eliminar el bucle de cálculo de inasistencias después de consultar alumnos
3. Eliminar el `<th>` de inasistencias
4. En tbody, restaurar:
   - Nombre sin link (solo con htmlspecialchars)
   - Eliminar la nueva columna `<td>` con inasistencias

---

## 🔄 FLUJO DE DATOS

### 1. En Perfil del Alumno (`ALUMNO/perfil.php`)
```
Usuario accede a perfil
    ↓
Consulta tabla alumno
    ↓
Llama obtenerResumenInasistenciasPorMateria()
    ↓
Obtiene conteo de Ausentes, Retardos, Justificantes por materia
    ↓
Muestra tabla con estadísticas
```

### 2. En Lista de Alumnos Admin (`administrador/listaAlumnos.php`)
```
Admin accede a grupo/materia
    ↓
Consulta alumnos del grupo
    ↓
Para cada alumno, llama obtenerInasistenciasPorMateria()
    ↓
Muestra tabla con columna "Inasist."
    ↓
Usuario hace clic en nombre alumno
    ↓
Lleva a detalleInasistencias.php
```

### 3. En Detalle de Inasistencias (`administrador/detalleInasistencias.php`)
```
Se reciben parámetros: idAlumno e idMateria
    ↓
Obtiene datos del alumno y materia
    ↓
Obtiene inasistencias de ESA materia
    ↓
Muestra tarjetas con estadísticas
    ↓
Muestra tabla con historial completo (con fechas)
    ↓
📊 NUEVO: Obtiene resumen de TODAS las materias
    ↓
Muestra tabla resumida con inasistencias por materia
```

---

## 📌 NOTAS IMPORTANTES

### Búsquedas que NO se tocaron:
- Base de datos (sin cambios)
- Estructura de sesiones (sin cambios)
- Lógica de guardado de asistencias (sin cambios)
- Vistas de docentes (sin cambios)

### Consultas SQL utilizadas:
Todas están optimizadas con:
- Prepared statements para evitar SQL injection
- COUNT() con CASE WHEN para contar por estado
- LEFT JOIN para incluir alumnos sin registros
- ORDER BY para ordenamiento consistente

### Colores utilizados:
- **Ausentes:** #ff6b6b (Rojo)
- **Retardos:** #ffa500 (Naranja)
- **Justificantes:** #4da6ff (Azul)
- **Presentes:** #4caf50 (Verde)

---

## 🔧 CÓMO REVERTIR CAMBIOS

Si algo no te gusta, aquí está el orden para revertir:

**Opción 1: Revertir TODO**
```
1. Restaurar ALUMNO/perfil.php a versión anterior
2. Restaurar ALUMNO/css/perfil.css a versión anterior
3. Restaurar administrador/listaAlumnos.php a versión anterior
4. Eliminar administrador/detalleInasistencias.php
5. Eliminar assets/sentenciasSQL/asistenciaFunciones.php
```

**Opción 2: Revertir solo perfil del alumno**
```
1. Restaurar ALUMNO/perfil.php
2. Restaurar ALUMNO/css/perfil.css
```

**Opción 3: Revertir solo vista de admin**
```
1. Restaurar administrador/listaAlumnos.php
2. Eliminar administrador/detalleInasistencias.php
```

---

## ✨ CARACTERÍSTICAS IMPLEMENTADAS

✅ Contador de inasistencias en perfil del alumno  
✅ Tabla de inasistencias por materia en perfil  
✅ Columna de inasistencias en lista de admin  
✅ Página de detalle con historial completo  
✅ Estadísticas visuales con tarjetas de colores  
✅ Links clickeables para ver detalles  
✅ Soporte para modo oscuro en todas las vistas  
✅ Diseño responsivo para móviles  
✅ Colores intuitivos según tipo de inasistencia  
✅ Documentación completa para revertir  

---

**Fin del documento de cambios**
