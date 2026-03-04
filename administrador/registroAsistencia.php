<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/Conexion.php";

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];
$hoy = date('d/m/Y');
$horaActual = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Diario de Asistencia — CECYTEM</title>
    <link rel="stylesheet" href="css/menu.css?v=2.1">
    <link rel="stylesheet" href="css/registroAsistencia.css?v=1.2">
</head>
<body>

<div class="contenedor">
  <div class="contenedor1" style="min-height:140px;height:auto;">
    <video autoplay muted loop id="videoFondo" style="height:140px;">
      <source src="img/video.mp4" type="video/mp4">
    </video>
    <?php include_once "layout/header_admin.php"; ?>
  </div>
</div>

<main>
<div class="ra-main">

  <!-- Cabecera módulo -->
  <div class="ra-titulo">
    <div class="ra-titulo-icono">📋</div>
    <div>
      <h1>Registro Diario de Asistencia</h1>
      <p>Ingresa o escanea la matrícula (número de control) del alumno</p>
    </div>
    <div class="ra-fecha-badge">
      <span class="dot"></span>
      <span id="reloj-live"><?= $hoy . '  ' . $horaActual ?></span>
    </div>
  </div>

  <!-- Pestañas principales -->
  <div class="ra-tabs">
    <button class="ra-tab activa" onclick="cambiarTab('registro', this)">
      🔍 Registro de Asistencia
    </button>
    <button class="ra-tab" onclick="cambiarTab('control', this)">
      🗄️ Control / Base de Datos
    </button>
  </div>

  <!-- ══════════ PANEL 1: REGISTRO ══════════ -->
  <div id="panel-registro" class="ra-panel activo">
    <div class="ra-grid">

      <!-- Columna izquierda: input + tarjeta -->
      <div>
        <div class="ra-card" style="margin-bottom:1.2rem;">
          <div class="ra-card-header">
            <span class="ra-card-title">🔢 Número de Control</span>
            <span style="font-size:.75rem;color:var(--muted);">Matrícula del alumno</span>
          </div>
          <div class="ra-card-body">
            <label class="ra-label">Matrícula / Número de Control</label>
            <div class="ra-mat-row">
              <input type="text" id="inp-matricula" class="ra-mat-input"
                     placeholder="Ej. 23415082610076" maxlength="20"
                     autocomplete="off"
                     onkeydown="if(event.key==='Enter') buscarAlumno()">
              <button class="ra-btn-buscar" onclick="buscarAlumno()">Buscar</button>
            </div>
            <p class="ra-hint">💡 Compatible con lector de código de barras — presiona Enter para buscar</p>
          </div>
        </div>

        <div id="ra-empty" class="ra-empty">
          <div class="ra-empty-icono">🎓</div>
          <p>Ingresa o escanea una matrícula<br>para mostrar la información del alumno</p>
        </div>

        <div id="ra-loader" class="ra-loader">
          <div class="ra-spinner"></div> Buscando alumno...
        </div>

        <!-- Tarjeta alumno -->
        <div id="ra-alumno-card" class="ra-alumno-card">
          <div class="ra-alumno-top">
            <div class="ra-avatar" id="av-iniciales">--</div>
            <div class="ra-info">
              <div class="ra-mat-tag" id="av-matricula">No. Control: ---</div>
              <h3 id="av-nombre">---</h3>
              <div class="ra-badges">
                <span class="ra-badge rb-grupo" id="av-grupo">---</span>
                <span class="ra-badge rb-lista" id="av-lista">Lista: --</span>
              </div>
            </div>
            <div class="ra-ya-tag" id="ra-ya-tag">✓ Ya registrado hoy</div>
          </div>

          <!-- Stats: diaria + materias -->
          <div class="ra-stats-mini" style="grid-template-columns:repeat(4,1fr);">
            <div class="ra-stat-mini">
              <div class="val v-verde" id="st-presentes">0</div>
              <div class="lbl">Días Presente</div>
            </div>
            <div class="ra-stat-mini">
              <div class="val v-amarillo" id="st-tardios">0</div>
              <div class="lbl">Tardanzas</div>
            </div>
            <div class="ra-stat-mini">
              <div class="val" id="st-total" style="color:var(--cafe-medio);">0</div>
              <div class="lbl">Días registrados</div>
            </div>
            <div class="ra-stat-mini">
              <div class="val v-rojo" id="st-faltas-mat">0</div>
              <div class="lbl">Faltas materia</div>
            </div>
          </div>

          <div class="ra-historial">
            <h4>Últimos 5 registros diarios</h4>
            <div id="ra-hist-list">
              <div style="font-size:.8rem;color:var(--muted);text-align:center;padding:.5rem;">Sin registros previos</div>
            </div>
          </div>

          <!-- Botones: solo los 2 estados del ENUM de asistencia_diaria -->
          <div class="ra-acciones" style="grid-template-columns:1fr 1fr;">
            <button class="ra-btn-accion ra-btn-presente" id="btn-presente"
                    onclick="registrar('Presente')" disabled>
              ✅ Presente
            </button>
            <button class="ra-btn-accion ra-btn-retardo" id="btn-tardio"
                    onclick="registrar('Tardío')" disabled>
              ⏰ Tardío
            </button>
          </div>
        </div>
      </div>

      <!-- Columna derecha: stats del día + lista -->
      <div>
        <div class="ra-stat-cards">
          <div class="ra-stat-card ra-sc-total">
            <div class="num" id="dia-total" style="color:var(--cafe-medio);">0</div>
            <div class="lbl">Registrados hoy</div>
          </div>
          <div class="ra-stat-card ra-sc-presente">
            <div class="num v-verde" id="dia-presentes">0</div>
            <div class="lbl">Presentes</div>
          </div>
          <div class="ra-stat-card ra-sc-retardo">
            <div class="num v-amarillo" id="dia-tardios">0</div>
            <div class="lbl">Tardíos</div>
          </div>
          <div class="ra-stat-card ra-sc-ausente">
            <div class="num v-rojo" id="dia-sin">0</div>
            <div class="lbl">Sin registrar</div>
          </div>
        </div>

        <div class="ra-card">
          <div class="ra-card-header">
            <span class="ra-card-title">📅 Registros de Hoy</span>
            <span id="hoy-count" style="font-size:.75rem;color:var(--muted);">0 entradas</span>
          </div>
          <div class="ra-card-body" style="max-height:420px;overflow-y:auto;">
            <div id="hoy-lista">
              <div class="ra-empty" style="padding:1.5rem;border:none;background:none;">
                <p>Sin registros aún hoy</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div><!-- /panel-registro -->

  <!-- ══════════ PANEL 2: CONTROL ══════════ -->
  <div id="panel-control" class="ra-panel">

    <!-- Sub-pestañas: Diaria / Por Materia -->
    <div class="ra-subtabs" style="display:flex;gap:.5rem;margin-bottom:1.2rem;">
      <button class="ra-subtab activa" onclick="cambiarSubtab('diaria', this)">
        📅 Asistencia Diaria General
      </button>
      <button class="ra-subtab" onclick="cambiarSubtab('materia', this)">
        📚 Asistencia por Materia
      </button>
    </div>

    <!-- ── Sub-panel DIARIA ── -->
    <div id="subpanel-diaria">
      <div class="ra-toolbar">
        <input type="text" class="ra-input-filtro ra-search" id="d-buscar"
               placeholder="🔍 Buscar nombre o matrícula...">
        <input type="date" class="ra-input-filtro" id="d-fecha">
        <select class="ra-input-filtro" id="d-grupo">
          <option value="">Todos los grupos</option>
        </select>
        <select class="ra-input-filtro" id="d-estado">
          <option value="">Todos los estados</option>
          <option value="Presente">✅ Presente</option>
          <option value="Tardío">⏰ Tardío</option>
        </select>
        <button class="ra-btn-filtrar" onclick="cargarDiaria()">Filtrar</button>
        <button class="ra-btn-exportar" onclick="exportarCSV('diaria')">⬇ CSV</button>
      </div>

      <div class="ra-stat-cards" style="margin-bottom:1.2rem;">
        <div class="ra-stat-card ra-sc-total">
          <div class="num" id="d-total" style="color:var(--cafe-medio);">—</div>
          <div class="lbl">Total registros</div>
        </div>
        <div class="ra-stat-card ra-sc-presente">
          <div class="num v-verde" id="d-presentes">—</div>
          <div class="lbl">Presentes</div>
        </div>
        <div class="ra-stat-card ra-sc-retardo">
          <div class="num v-amarillo" id="d-tardios">—</div>
          <div class="lbl">Tardíos</div>
        </div>
        <div class="ra-stat-card">
          <div class="num" id="d-dispositivo" style="color:var(--cafe-medio);font-size:1.1rem;">—</div>
          <div class="lbl">Fuente</div>
        </div>
      </div>

      <div class="ra-table-wrap">
        <table class="ra-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Matrícula</th>
              <th>Nombre Completo</th>
              <th>Grupo</th>
              <th>Fecha</th>
              <th>Hora Entrada</th>
              <th>Estado</th>
              <th>Dispositivo</th>
            </tr>
          </thead>
          <tbody id="d-tbody">
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--muted);">
              Usa los filtros y presiona <strong>Filtrar</strong>
            </td></tr>
          </tbody>
        </table>
        <div class="ra-table-footer">
          <span id="d-info">— registros</span>
          <span style="font-size:.75rem;">Fuente: tabla <code>asistencia_diaria</code></span>
        </div>
      </div>
    </div>

    <!-- ── Sub-panel MATERIA ── -->
    <div id="subpanel-materia" style="display:none;">
      <div class="ra-toolbar">
        <input type="text" class="ra-input-filtro ra-search" id="m-buscar"
               placeholder="🔍 Buscar nombre o matrícula...">
        <input type="date" class="ra-input-filtro" id="m-fecha">
        <select class="ra-input-filtro" id="m-grupo">
          <option value="">Todos los grupos</option>
        </select>
        <select class="ra-input-filtro" id="m-estado">
          <option value="">Todos los estados</option>
          <option value="Ausente">❌ Ausente</option>
          <option value="Retardo">⏰ Retardo</option>
          <option value="Justificante">📄 Justificante</option>
        </select>
        <button class="ra-btn-filtrar" onclick="cargarMateria()">Filtrar</button>
        <button class="ra-btn-exportar" onclick="exportarCSV('materia')">⬇ CSV</button>
      </div>

      <div class="ra-stat-cards" style="margin-bottom:1.2rem;">
        <div class="ra-stat-card ra-sc-total">
          <div class="num" id="m-total" style="color:var(--cafe-medio);">—</div>
          <div class="lbl">Total registros</div>
        </div>
        <div class="ra-stat-card ra-sc-ausente">
          <div class="num v-rojo" id="m-ausentes">—</div>
          <div class="lbl">Ausentes</div>
        </div>
        <div class="ra-stat-card ra-sc-retardo">
          <div class="num v-amarillo" id="m-retardos">—</div>
          <div class="lbl">Retardos</div>
        </div>
        <div class="ra-stat-card">
          <div class="num v-azul" id="m-justificados">—</div>
          <div class="lbl">Justificantes</div>
        </div>
      </div>

      <div class="ra-table-wrap">
        <table class="ra-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Matrícula</th>
              <th>Nombre Completo</th>
              <th>Grupo</th>
              <th>Materia</th>
              <th>Fecha</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody id="m-tbody">
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">
              Usa los filtros y presiona <strong>Filtrar</strong>
            </td></tr>
          </tbody>
        </table>
        <div class="ra-table-footer">
          <span id="m-info">— registros</span>
          <span style="font-size:.75rem;">Fuente: tabla <code>asistencia</code> (original sin cambios)</span>
        </div>
      </div>
    </div>

  </div><!-- /panel-control -->

</div>
</main>

<?php include_once "layout/footer_admin.php"; ?>

<!-- Toast -->
<div class="ra-toast" id="ra-toast">
  <div class="t-icono" id="t-icono">✅</div>
  <div>
    <div class="t-titulo" id="t-titulo">OK</div>
    <div class="t-sub"    id="t-sub"></div>
  </div>
</div>

<style>
/* Sub-pestañas de control */
.ra-subtab {
    padding: .55rem 1.2rem;
    background: var(--crema-card);
    border: 1.5px solid var(--borde);
    border-radius: var(--radius-sm);
    font-family: 'Segoe UI', sans-serif;
    font-size: .86rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s;
}
.ra-subtab:hover  { color: var(--cafe-medio); border-color: var(--cafe-medio); }
.ra-subtab.activa { background: rgba(139,69,19,.1); border-color: var(--cafe-medio); color: var(--cafe-medio); }
</style>

<script>
// ── Estado global ──
let alumnoActual  = null;
let registrosHoy  = [];
let tablaDataD    = [];   // datos diaria
let tablaDataM    = [];   // datos materia
let subtabActual  = 'diaria';

// ── Reloj ──
setInterval(() => {
    const n = new Date();
    document.getElementById('reloj-live').textContent =
        n.toLocaleDateString('es-MX', {day:'2-digit',month:'2-digit',year:'numeric'})
        + '  ' + n.toLocaleTimeString('es-MX');
}, 1000);

// ── Tabs principales ──
function cambiarTab(panel, btn) {
    document.querySelectorAll('.ra-panel').forEach(p => p.classList.remove('activo'));
    document.querySelectorAll('.ra-tab').forEach(b => b.classList.remove('activa'));
    document.getElementById('panel-' + panel).classList.add('activo');
    btn.classList.add('activa');
    if (panel === 'control') iniciarControl();
}

// ── Sub-tabs control ──
function cambiarSubtab(sub, btn) {
    subtabActual = sub;
    document.querySelectorAll('.ra-subtab').forEach(b => b.classList.remove('activa'));
    btn.classList.add('activa');
    document.getElementById('subpanel-diaria').style.display  = sub === 'diaria'  ? '' : 'none';
    document.getElementById('subpanel-materia').style.display = sub === 'materia' ? '' : 'none';
}

function iniciarControl() {
    // Establecer fecha de hoy en ambos filtros
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('d-fecha').value = hoy;
    document.getElementById('m-fecha').value = hoy;
    cargarDiaria();
    cargarGruposSelect();
}

// ── Toast ──
function toast(tipo, titulo, sub) {
    const t = document.getElementById('ra-toast');
    const ic = {ok:'✅', warn:'⏰', error:'❌', info:'ℹ️'};
    t.className = 'ra-toast mostrar t-' + tipo;
    document.getElementById('t-icono').textContent  = ic[tipo] || '✅';
    document.getElementById('t-titulo').textContent = titulo;
    document.getElementById('t-sub').textContent    = sub;
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('mostrar'), 3500);
}

// ── Helpers ──
function iniciales(nombre, apellidos) {
    return ((nombre?.[0] || '') + (apellidos?.[0] || '')).toUpperCase();
}
function pillDiaria(estado) {
    const m = {'Presente':'pe-presente','Tardío':'pe-retardo'};
    return `<span class="pill-estado ${m[estado]||'pe-presente'}">${estado}</span>`;
}
function pillMateria(estado) {
    const m = {'Ausente':'pe-ausente','Retardo':'pe-retardo','Justificante':'pe-justificante'};
    return `<span class="pill-estado ${m[estado]||'pe-ausente'}">${estado}</span>`;
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Buscar alumno ──
function buscarAlumno() {
    const mat = document.getElementById('inp-matricula').value.trim();
    if (!mat) { toast('error', 'Campo vacío', 'Ingresa una matrícula'); return; }

    document.getElementById('ra-empty').style.display = 'none';
    document.getElementById('ra-alumno-card').classList.remove('visible');
    document.getElementById('ra-loader').classList.add('visible');

    fetch('ajax/buscarAlumno.php?matricula=' + encodeURIComponent(mat))
        .then(r => r.json())
        .then(data => {
            document.getElementById('ra-loader').classList.remove('visible');
            if (data.error) {
                toast('error', 'No encontrado', data.error);
                document.getElementById('ra-empty').style.display = '';
                alumnoActual = null;
                setBotones(true);
                return;
            }
            mostrarAlumno(data);
        })
        .catch(() => {
            document.getElementById('ra-loader').classList.remove('visible');
            document.getElementById('ra-empty').style.display = '';
            toast('error', 'Error de conexión', 'No se pudo contactar al servidor');
        });
}

function mostrarAlumno(data) {
    alumnoActual = data.alumno;
    const al = data.alumno;
    const yaReg = !!data.registroHoy;

    document.getElementById('av-iniciales').textContent = iniciales(al.nombre, al.apellidos);
    document.getElementById('av-matricula').textContent = 'No. Control: ' + al.matricula;
    document.getElementById('av-nombre').textContent    = al.apellidos + ' ' + al.nombre;
    document.getElementById('av-grupo').textContent     = al.nombre_grupo || 'Sin grupo';
    document.getElementById('av-lista').textContent     = 'Lista: ' + (al.numero_lista || '—');

    const s = data.stats || {};
    document.getElementById('st-presentes').textContent  = s.presentes      || 0;
    document.getElementById('st-tardios').textContent    = s.tardios        || 0;
    document.getElementById('st-total').textContent      = s.total_dias     || 0;
    document.getElementById('st-faltas-mat').textContent = s.faltas_materia || 0;

    const yaTag = document.getElementById('ra-ya-tag');
    yaTag.classList.toggle('visible', yaReg);
    if (yaReg && data.registroHoy) {
        yaTag.textContent = '✓ ' + data.registroHoy.estado + ' — ' + (data.registroHoy.hora_entrada?.substr(0,5) || '');
    }

    const hl = document.getElementById('ra-hist-list');
    if (data.historial?.length) {
        hl.innerHTML = data.historial.map(h => `
            <div class="ra-hist-item">
                <span class="ra-hist-fecha">${h.fecha}</span>
                <span class="ra-hist-hora">${h.hora_entrada ? h.hora_entrada.substr(0,5) : ''}</span>
                ${pillDiaria(h.estado)}
            </div>`).join('');
    } else {
        hl.innerHTML = '<div style="font-size:.8rem;color:var(--muted);text-align:center;padding:.5rem;">Sin registros previos</div>';
    }

    setBotones(yaReg);
    document.getElementById('ra-alumno-card').classList.add('visible');
}

function setBotones(disabled) {
    document.getElementById('btn-presente').disabled = disabled;
    document.getElementById('btn-tardio').disabled   = disabled;
}

// ── Registrar asistencia diaria ──
function registrar(estado) {
    if (!alumnoActual) return;
    setBotones(true);

    fetch('ajax/guardarAsistenciaDiaria.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_alumno: alumnoActual.id_alumno, estado, dispositivo: 'Manual'})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) {
            toast('error', 'Error al guardar', data.msg || '');
            setBotones(false);
            return;
        }

        const yaTag = document.getElementById('ra-ya-tag');
        yaTag.textContent = '✓ ' + estado + ' — ' + data.hora.substr(0,5);
        yaTag.classList.add('visible');

        registrosHoy.unshift({
            matricula: alumnoActual.matricula,
            nombre:    alumnoActual.apellidos + ' ' + alumnoActual.nombre,
            hora:      data.hora.substr(0,5),
            estado
        });
        renderHoyLista();
        actualizarStatsHoy();

        const tipos = {Presente:'ok', 'Tardío':'warn'};
        toast(tipos[estado]||'ok', estado + ' registrado',
            alumnoActual.apellidos + ', ' + alumnoActual.nombre + ' — ' + data.hora.substr(0,5));

        setTimeout(() => {
            document.getElementById('inp-matricula').value = '';
            document.getElementById('inp-matricula').focus();
            document.getElementById('ra-alumno-card').classList.remove('visible');
            document.getElementById('ra-empty').style.display = '';
            alumnoActual = null;
        }, 2000);
    })
    .catch(() => { toast('error','Error de conexión',''); setBotones(false); });
}

function renderHoyLista() {
    const el = document.getElementById('hoy-lista');
    if (!registrosHoy.length) {
        el.innerHTML = '<div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Sin registros aún hoy</p></div>';
        return;
    }
    el.innerHTML = registrosHoy.map(r => `
        <div class="ra-hoy-item">
            <span class="ra-hoy-mat">${r.matricula}</span>
            <span class="ra-hoy-nombre">${r.nombre}</span>
            <span class="ra-hoy-hora">${r.hora}</span>
            ${pillDiaria(r.estado)}
        </div>`).join('');
    document.getElementById('hoy-count').textContent =
        registrosHoy.length + ' entrada' + (registrosHoy.length !== 1 ? 's' : '');
}

function actualizarStatsHoy() {
    document.getElementById('dia-total').textContent    = registrosHoy.length;
    document.getElementById('dia-presentes').textContent = registrosHoy.filter(r=>r.estado==='Presente').length;
    document.getElementById('dia-tardios').textContent   = registrosHoy.filter(r=>r.estado==='Tardío').length;
}

// ── Control: cargar grupos en selects ──
function cargarGruposSelect() {
    fetch('ajax/obtenerRegistros.php?tipo=diaria&fecha=' + new Date().toISOString().split('T')[0])
        .then(r => r.json())
        .then(data => {
            ['d-grupo','m-grupo'].forEach(id => {
                const sel = document.getElementById(id);
                while (sel.options.length > 1) sel.remove(1);
                (data.grupos || []).forEach(g => sel.add(new Option(g.nombre, g.idGrupo)));
            });
        }).catch(()=>{});
}

// ── Cargar asistencia DIARIA ──
function cargarDiaria() {
    const p = new URLSearchParams({
        tipo:   'diaria',
        buscar: document.getElementById('d-buscar').value.trim(),
        fecha:  document.getElementById('d-fecha').value,
        grupo:  document.getElementById('d-grupo').value,
        estado: document.getElementById('d-estado').value,
    });
    setLoading('d-tbody', 8);

    fetch('ajax/obtenerRegistros.php?' + p)
        .then(r => r.json())
        .then(data => {
            if (data.error) { setError('d-tbody', 8, data.error); return; }
            tablaDataD = data.registros || [];

            const tbody = document.getElementById('d-tbody');
            if (!tablaDataD.length) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--muted);">Sin registros encontrados</td></tr>';
            } else {
                tbody.innerHTML = tablaDataD.map((r,i) => `
                    <tr>
                        <td style="color:var(--muted);font-size:.74rem;">${i+1}</td>
                        <td class="td-mat">${esc(r.matricula)}</td>
                        <td>${esc(r.apellidos + ' ' + r.nombre)}</td>
                        <td style="font-size:.8rem;">${esc(r.grupo||'—')}</td>
                        <td class="td-hora">${esc(r.fecha)}</td>
                        <td class="td-hora">${r.hora_entrada ? esc(r.hora_entrada.substr(0,5)) : '—'}</td>
                        <td>${pillDiaria(r.estado)}</td>
                        <td style="font-size:.74rem;color:var(--muted);">${esc(r.dispositivo||'—')}</td>
                    </tr>`).join('');
            }

            const s = data.stats || {};
            document.getElementById('d-total').textContent    = s.total    || 0;
            document.getElementById('d-presentes').textContent = s.presentes|| 0;
            document.getElementById('d-tardios').textContent   = s.tardios  || 0;
            document.getElementById('d-dispositivo').textContent = tablaDataD.length ? tablaDataD[0].dispositivo || '—' : '—';
            document.getElementById('d-info').textContent = tablaDataD.length + ' registros';
        })
        .catch(() => setError('d-tbody', 8, 'Error de conexión'));
}

// ── Cargar asistencia POR MATERIA ──
function cargarMateria() {
    const p = new URLSearchParams({
        tipo:   'materia',
        buscar: document.getElementById('m-buscar').value.trim(),
        fecha:  document.getElementById('m-fecha').value,
        grupo:  document.getElementById('m-grupo').value,
        estado: document.getElementById('m-estado').value,
    });
    setLoading('m-tbody', 7);

    fetch('ajax/obtenerRegistros.php?' + p)
        .then(r => r.json())
        .then(data => {
            if (data.error) { setError('m-tbody', 7, data.error); return; }
            tablaDataM = data.registros || [];

            const tbody = document.getElementById('m-tbody');
            if (!tablaDataM.length) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">Sin registros encontrados</td></tr>';
            } else {
                tbody.innerHTML = tablaDataM.map((r,i) => `
                    <tr>
                        <td style="color:var(--muted);font-size:.74rem;">${i+1}</td>
                        <td class="td-mat">${esc(r.matricula)}</td>
                        <td>${esc(r.apellidos + ' ' + r.nombre)}</td>
                        <td style="font-size:.8rem;">${esc(r.grupo||'—')}</td>
                        <td style="font-size:.8rem;color:var(--cafe-oscuro);">${esc(r.materia||'—')}</td>
                        <td class="td-hora">${esc(r.fecha)}</td>
                        <td>${pillMateria(r.estado)}</td>
                    </tr>`).join('');
            }

            const s = data.stats || {};
            document.getElementById('m-total').textContent       = s.total        || 0;
            document.getElementById('m-ausentes').textContent    = s.ausentes     || 0;
            document.getElementById('m-retardos').textContent    = s.retardos     || 0;
            document.getElementById('m-justificados').textContent = s.justificados || 0;
            document.getElementById('m-info').textContent = tablaDataM.length + ' registros';
        })
        .catch(() => setError('m-tbody', 7, 'Error de conexión'));
}

function setLoading(tbodyId, cols) {
    document.getElementById(tbodyId).innerHTML =
        `<tr><td colspan="${cols}" style="text-align:center;padding:2rem;color:var(--muted);">
            <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;">
                <div class="ra-spinner"></div> Cargando...
            </div></td></tr>`;
}
function setError(tbodyId, cols, msg) {
    document.getElementById(tbodyId).innerHTML =
        `<tr><td colspan="${cols}" style="text-align:center;padding:2rem;color:var(--rojo);">${msg}</td></tr>`;
}

// ── Exportar CSV ──
function exportarCSV(tipo) {
    const data = tipo === 'diaria' ? tablaDataD : tablaDataM;
    if (!data.length) { toast('warn','Sin datos','Filtra primero los registros'); return; }

    let headers, rows;
    if (tipo === 'diaria') {
        headers = ['Matrícula','Nombre','Apellidos','Grupo','Fecha','Hora','Estado','Dispositivo'];
        rows = data.map(r => [r.matricula, r.nombre, r.apellidos, r.grupo||'', r.fecha,
                               r.hora_entrada?.substr(0,5)||'', r.estado, r.dispositivo||'']);
    } else {
        headers = ['Matrícula','Nombre','Apellidos','Grupo','Materia','Fecha','Estado'];
        rows = data.map(r => [r.matricula, r.nombre, r.apellidos, r.grupo||'', r.materia||'', r.fecha, r.estado]);
    }

    const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
    a.download = `asistencia_${tipo}_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    toast('ok','CSV exportado', data.length + ' registros descargados');
}

// ── Init ──
document.getElementById('inp-matricula').focus();
</script>
</body>
</html>
