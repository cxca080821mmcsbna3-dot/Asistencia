# 📚 ÍNDICE RÁPIDO - SISTEMA DE INASISTENCIAS

## 🎯 Acceso rápido a la documentación

### 📖 Documentos principales:
1. **[RESUMEN_CAMBIOS.md](RESUMEN_CAMBIOS.md)** ⭐ EMPIEZA AQUÍ
   - Qué se hizo en 2 minutos
   - Vistas nuevas
   - Flujos de datos
   - Características

2. **[DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md](DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md)** 🔧 PARA TÉCNICOS
   - Cada línea de código modificada
   - Parámetros nuevos
   - Cómo revertir cada cambio
   - Consultas SQL utilizadas

3. **[verificar_instalacion.php](verificar_instalacion.php)** ✅ VERIFICADOR
   - Comprueba que todo se instaló
   - Accede desde el navegador
   - URL: `http://localhost/Asistencia/verificar_instalacion.php`

---

## 🗂️ Estructura de archivos

```
Asistencia/
├── 📄 RESUMEN_CAMBIOS.md (nuevo)
├── 📄 DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md (nuevo)
├── 📄 INDICE_REFERENCIA.md (este archivo)
├── 📄 verificar_instalacion.php (nuevo)
│
├── ALUMNO/
│   ├── perfil.php (MODIFICADO - sección inasistencias)
│   └── css/
│       └── perfil.css (MODIFICADO - estilos inasistencias)
│
├── administrador/
│   ├── listaAlumnos.php (MODIFICADO - columna inasistencias)
│   └── detalleInasistencias.php (NUEVO - página detalle)
│
└── assets/sentenciasSQL/
    └── asistenciaFunciones.php (NUEVO - funciones reutilizables)
```

---

## 🎬 Comenzando

### Para el usuario final (alumno):
1. Accede con tu cuenta
2. Ve a tu perfil
3. ¡Verás tu resumen de inasistencias!

### Para el administrador:
1. Ve a una materia y grupo
2. ¡Verás la columna de inasistencias!
3. Haz clic en un alumno para ver detalles

### Para el desarrollador:
1. Lee `RESUMEN_CAMBIOS.md`
2. Consulta `DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md` para detalles
3. Revisa el código de `asistenciaFunciones.php`
4. Usa `verificar_instalacion.php` para verificar

---

## 🔧 Funciones disponibles

### Ubicación: `assets/sentenciasSQL/asistenciaFunciones.php`

```php
// Total de inasistencias de un alumno en todas las materias
obtenerTotalInasistencias($pdo, $id_alumno) → int

// Inasistencias en una materia específica
obtenerInasistenciasPorMateria($pdo, $id_alumno, $id_materia) → int

// Resumen completo por materia
obtenerResumenInasistenciasPorMateria($pdo, $id_alumno) → array

// Inasistencias de todos los alumnos en grupo/materia
obtenerInasistenciasGrupoMateria($pdo, $id_materia, $id_grupo) → array

// Historial de inasistencias con fechas
obtenerHistorialInasistencias($pdo, $id_alumno, $id_materia) → array

// Inasistencias en un período específico (mes/año)
obtenerInasistenciasEnPeriodo($pdo, $id_alumno, $id_materia, $mes, $anio) → int

// Todos los alumnos de un grupo con conteos
obtenerInasistenciasGrupo($pdo, $id_grupo) → array
```

**Uso:**
```php
require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";

// Ejemplo 1: Total de inasistencias
$total = obtenerTotalInasistencias($pdo, 5); // 7

// Ejemplo 2: Por materia
$porMateria = obtenerInasistenciasPorMateria($pdo, 5, 2); // 3

// Ejemplo 3: Resumen
$resumen = obtenerResumenInasistenciasPorMateria($pdo, 5);
// Devuelve:
// [
//   ['id_materia' => 1, 'nombre' => 'Matemáticas', 'inasistencias' => 2, ...],
//   ['id_materia' => 2, 'nombre' => 'Historia', 'inasistencias' => 5, ...]
// ]
```

---

## 🎨 Estilos y colores

```css
/* Colores usados */
Ausentes:      #ff6b6b (Rojo)
Retardos:      #ffa500 (Naranja)
Justificantes: #4da6ff (Azul)
Presentes:     #4caf50 (Verde)
Fondo normal:  #f0e8dc (Crema)
Fondo oscuro:  #2c2c2c
```

### Clases CSS nuevas:
- `.inasistencias-seccion` - Contenedor principal
- `.inasistencias-total` - Badge del total
- `.total-badge` - Estilo del círculo rojo
- `.inasistencias-tabla` - Tabla de detalles
- `.ausentes`, `.retardos`, `.justificantes` - Colores de celda

---

## 🔍 Buscar cambios en el código

### En perfil.php, busca:
- `require_once __DIR__ . "/../assets/sentenciasSQL/asistenciaFunciones.php";`
- `obtenerResumenInasistenciasPorMateria()`
- `<div class="perfil-seccion inasistencias-seccion">`

### En listaAlumnos.php, busca:
- `asistenciaFunciones.php`
- `obtenerInasistenciasPorMateria()`
- `detalleInasistencias.php`
- `⚠️ Inasist.` (encabezado de columna)

### En perfil.css, busca:
- `ESTILOS PARA INASISTENCIAS`
- `.inasistencias-tabla`
- `.total-badge`

---

## 🐛 Si algo falla

### El perfil no muestra inasistencias
1. Verifica que `asistenciaFunciones.php` existe
2. Comprueba que hay registros en la tabla `asistencia`
3. Revisa la consola del navegador (F12)

### La columna no aparece en listaAlumnos
1. Recarga la página (Ctrl+F5)
2. Verifica que la modificación en listaAlumnos.php se guardó
3. Busca el encabezado `⚠️ Inasist.` en el HTML

### detalleInasistencias da error 404
1. Verifica que el archivo existe en `administrador/`
2. Comprueba que pasas parámetros GET: `?idAlumno=X&idMateria=Y`
3. Asegúrate de que esos IDs existen en la BD

### Modo oscuro no funciona
1. Verifica que localStorage está disponible
2. Abre DevTools y ejecuta: `localStorage.setItem("modo", "oscuro")`
3. Recarga la página

---

## 📞 Preguntas frecuentes

**P: ¿Se modificó la base de datos?**  
R: No. Solo se usan consultas SELECT con las tablas existentes.

**P: ¿Se puede revertir fácilmente?**  
R: Sí, sigue `DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md`

**P: ¿Afecta a otros módulos?**  
R: No. Los cambios son aislados a perfil y admin.

**P: ¿Qué pasa si hay mil alumnos?**  
R: Las consultas son eficientes. Se usan índices nativos de MySQL.

**P: ¿Funciona en móvil?**  
R: Sí, con diseño responsivo completo.

---

## ✅ Checklist de verificación

- [ ] Verificar que los 4 archivos nuevos existen
- [ ] Verificar que los 3 archivos fueron modificados
- [ ] Acceder como alumno y ver perfil
- [ ] Acceder como admin y ver lista de alumnos
- [ ] Hacer clic en alumno para ver detalle
- [ ] Probar modo oscuro
- [ ] Probar en móvil
- [ ] Consultar documentación si hay dudas
- [ ] Guarda una copia de seguridad si es necesario

---

## 📞 Soporte

Para problemas:
1. Consulta `DOCUMENTO_DE_CAMBIOS_INASISTENCIAS.md`
2. Ejecuta `verificar_instalacion.php`
3. Revisa los comentarios en el código (busca 📊 NUEVO)
4. Verifica que la BD tiene datos en tabla `asistencia`

---

**Última actualización:** 20 de enero de 2026  
**Versión:** 1.0  
**Desarrollado por:** GitHub Copilot  
**Estado:** ✅ Listo para usar
