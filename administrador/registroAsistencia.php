<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . "/../assets/sentenciasSQL/Conexion.php";
if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit;
}
$nombreAdmin = $_SESSION['nombre'];
$hoy = date('d/m/Y');
$horaActual = date('H:i:s');

// ── Modo incidencia por URL ──────────────────────────────────────────────
// Uso: registroAsistencia.php?modo=incidencia&tipo=Sin+uniforme
// La computadora que tenga esta URL registrará esa incidencia automáticamente
$modoIncidencia = trim($_GET['modo'] ?? '');
$tipoIncidencia = trim($_GET['tipo'] ?? '');
$modoActivo     = ($modoIncidencia === 'incidencia' && $tipoIncidencia !== '');
$incidenciaActiva = $modoActivo ? $tipoIncidencia : '';

// ── Cargar config WhatsApp (para pasar estado al JS) ────────────────────
$waFile   = __DIR__ . '/config/whatsapp.json';
$waActivo = false;
$waConfig = [];
if (file_exists($waFile)) {
    $waConfig = json_decode(file_get_contents($waFile), true) ?? [];
    $waActivo = (bool)($waConfig['activo'] ?? false);
}

// ── Cargar grupos para selector WhatsApp ────────────────────────────────
$stmtGrupos = $pdo->query("SELECT idGrupo, nombre FROM grupo ORDER BY nombre ASC");
$gruposWA   = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Diario de Asistencia — CECYTEM</title>
    <link rel="stylesheet" href="css/menu.css?v=2.1">
    <link rel="stylesheet" href="css/registroAsistencia.css?v=1.3">
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

  <!-- Cabecera -->
  <div class="ra-titulo">
    <div class="ra-titulo-icono">📋</div>
    <div>
      <h1>Registro Diario de Asistencia</h1>
      <p id="turno-activo-label">Cargando configuración de horario…</p>
    </div>
    <div style="display:flex;gap:.7rem;align-items:center;margin-left:auto;">
      <button class="ra-btn-config" onclick="abrirConfig()" title="Configurar horarios">
        ⚙️ Horarios
      </button>
      <div class="ra-fecha-badge">
        <span class="dot"></span>
        <span id="reloj-live"><?= $hoy . '  ' . $horaActual ?></span>
      </div>
    </div>
  </div>

  <!-- Banner modo incidencia (solo visible si ?modo=incidencia&tipo=...) -->
  <?php if ($modoActivo): ?>
  <div class="ra-modo-banner" id="modo-banner">
    <span class="ra-modo-icono">⚠️</span>
    <div>
      <strong>MODO INCIDENCIA ACTIVO</strong>
      <span id="modo-banner-tipo"> — <?= htmlspecialchars($incidenciaActiva) ?></span>
    </div>
    <span class="ra-modo-badge"><?= htmlspecialchars($incidenciaActiva) ?></span>
    <span style="font-size:.78rem;color:rgba(255,255,255,.75);margin-left:auto;">
      Todos los registros desde esta computadora quedarán marcados con esta incidencia
    </span>
  </div>
  <?php endif; ?>

  <!-- Pestañas principales -->
  <div class="ra-tabs">
    <button class="ra-tab activa" onclick="cambiarTab('registro',this)">🔍 Registro de Asistencia</button>
    <button class="ra-tab"       onclick="cambiarTab('control',this)">🗄️ Control / Base de Datos</button>
    <button class="ra-tab"       onclick="cambiarTab('whatsapp',this)">📱 WhatsApp<?= $waActivo ? ' <span class="wa-dot-activo">●</span>' : '' ?></button>
  </div>

  <!-- ══════════ PANEL 1: REGISTRO ══════════ -->
  <div id="panel-registro" class="ra-panel activo">
    <div class="ra-grid">

      <!-- Columna izquierda -->
      <div>
        <div class="ra-card" style="margin-bottom:1.2rem;">
          <div class="ra-card-header">
            <span class="ra-card-title">🔢 Número de Control</span>
            <div class="turno-chip" id="turno-chip">⏳ Detectando turno…</div>
          </div>
          <div class="ra-card-body">
            <label class="ra-label">Matrícula / Número de Control</label>
            <div class="ra-mat-row">
              <input type="text" id="inp-matricula" class="ra-mat-input"
                     placeholder="Ej. 23415082610076" maxlength="20" autocomplete="off"
                     onkeydown="if(event.key==='Enter') buscarAlumno()">
              <button class="ra-btn-buscar" onclick="buscarAlumno()">Buscar</button>
            </div>
            <p class="ra-hint">💡 Compatible con lector de código de barras · Enter para buscar</p>
          </div>
        </div>

        <!-- Indicador de estado que se asignará -->
        <div id="estado-preview" class="estado-preview" style="display:none;">
          <span id="estado-preview-icon">✅</span>
          <div>
            <div id="estado-preview-text" style="font-weight:700;font-size:.9rem;">Presente</div>
            <div id="estado-preview-sub" style="font-size:.74rem;color:var(--muted);">Dentro del horario</div>
          </div>
        </div>

        <div id="ra-empty" class="ra-empty">
          <div class="ra-empty-icono">📷</div>
          <p>Escanea el código QR o ingresa la matrícula<br>La asistencia se registra <strong>automáticamente</strong></p>
        </div>
        <div id="ra-loader" class="ra-loader"><div class="ra-spinner"></div> Buscando alumno…</div>

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
            <div class="ra-ya-tag" id="ra-ya-tag">✓ Ya registrado</div>
          </div>

          <div class="ra-stats-mini" style="grid-template-columns:repeat(4,1fr);">
            <div class="ra-stat-mini"><div class="val v-verde"  id="st-presentes">0</div><div class="lbl">Días Presente</div></div>
            <div class="ra-stat-mini"><div class="val v-amarillo" id="st-tardios">0</div><div class="lbl">Tardanzas</div></div>
            <div class="ra-stat-mini"><div class="val" id="st-total" style="color:var(--cafe-medio);">0</div><div class="lbl">Días registrados</div></div>
            <div class="ra-stat-mini"><div class="val v-rojo" id="st-faltas-mat">0</div><div class="lbl">Faltas materia</div></div>
          </div>

          <div class="ra-historial">
            <h4>Últimos 5 registros diarios</h4>
            <div id="ra-hist-list"><div style="font-size:.8rem;color:var(--muted);text-align:center;padding:.5rem;">Sin registros previos</div></div>
          </div>

          <!-- Botón oculto: el registro es automático al escanear.
               Solo visible si por algún error no se registró al buscar. -->
          <div style="padding:1.1rem 1.3rem;">
            <button class="ra-btn-registrar" id="btn-registrar" onclick="registrar()" disabled style="display:none;">
              <span id="btn-icon">✅</span> <span id="btn-text">Registrar Asistencia</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Columna derecha -->
      <div>
        <div class="ra-stat-cards">
          <div class="ra-stat-card ra-sc-total"><div class="num" id="dia-total" style="color:var(--cafe-medio);">0</div><div class="lbl">Registrados hoy</div></div>
          <div class="ra-stat-card ra-sc-presente"><div class="num v-verde" id="dia-presentes">0</div><div class="lbl">Presentes</div></div>
          <div class="ra-stat-card ra-sc-retardo" style="cursor:pointer;" onclick="cambiarListaHoy('tardios',document.getElementById('ltab-tardios'))" title="Ver tardíos">
            <div class="num v-amarillo" id="dia-tardios">0</div><div class="lbl">Tardíos</div>
          </div>
          <div class="ra-stat-card ra-sc-ausente" style="cursor:pointer;" onclick="cambiarListaHoy('faltantes',document.getElementById('ltab-faltantes'))" title="Ver sin registrar">
            <div class="num v-rojo" id="dia-sin">0</div><div class="lbl">Sin registrar</div>
          </div>
        </div>

        <div class="ra-card">
          <div class="ra-card-header" style="flex-wrap:wrap;gap:.5rem;">
            <span class="ra-card-title" id="hoy-card-titulo">📅 Registros de Hoy</span>
            <div class="ra-lista-tabs">
              <button class="ra-lista-tab activa" onclick="cambiarListaHoy('todos',this)" id="ltab-todos">Todos</button>
              <button class="ra-lista-tab"        onclick="cambiarListaHoy('tardios',this)" id="ltab-tardios">⏰ Tardíos</button>
              <button class="ra-lista-tab"        onclick="cambiarListaHoy('faltantes',this)" id="ltab-faltantes">⚠️ Sin registrar</button>
            </div>
          </div>
          <div class="ra-card-body" style="max-height:440px;overflow-y:auto;">
            <div id="lista-todos"><div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Sin registros aún hoy</p></div></div>
            <div id="lista-tardios"   style="display:none;"><div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Sin tardíos registrados</p></div></div>
            <div id="lista-faltantes" style="display:none;"><div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Cargando…</p></div></div>
          </div>
          <div style="padding:.5rem 1rem;border-top:1px solid var(--borde);font-size:.75rem;color:var(--muted);">
            <span id="hoy-count">0 entradas</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ PANEL 2: CONTROL ══════════ -->
  <div id="panel-control" class="ra-panel">

    <!-- Solo Asistencia Diaria General — el control por materia tiene su propio apartado -->
    <div id="subpanel-diaria">
      <div class="ra-toolbar">
        <input type="text"  class="ra-input-filtro ra-search" id="d-buscar" placeholder="🔍 Nombre o matrícula…">
        <input type="date"  class="ra-input-filtro" id="d-fecha">
        <select class="ra-input-filtro" id="d-grupo"><option value="">Todos los grupos</option></select>
        <select class="ra-input-filtro" id="d-estado">
          <option value="">Todos los estados</option>
          <option value="Presente">✅ Presente</option>
          <option value="Tardío">⏰ Tardío</option>
        </select>
        <select class="ra-input-filtro" id="d-incidencia">
          <option value="">Todas las incidencias</option>
          <option value="ninguna">Sin incidencia</option>
        </select>
        <button class="ra-btn-filtrar" onclick="cargarDiaria()">Filtrar</button>
        <button class="ra-btn-exportar" onclick="exportarCSV()">⬇ CSV</button>
      </div>
      <div class="ra-stat-cards" style="margin-bottom:1.2rem;">
        <div class="ra-stat-card ra-sc-total"><div class="num" id="d-total" style="color:var(--cafe-medio);">—</div><div class="lbl">Total</div></div>
        <div class="ra-stat-card ra-sc-presente"><div class="num v-verde" id="d-presentes">—</div><div class="lbl">Presentes</div></div>
        <div class="ra-stat-card ra-sc-retardo"><div class="num v-amarillo" id="d-tardios">—</div><div class="lbl">Tardíos</div></div>
        <div class="ra-stat-card" style="border-left:3px solid #e65100;">
          <div class="num" id="d-incidencias" style="color:#e65100;">—</div>
          <div class="lbl">Con incidencia</div>
        </div>
      </div>
      <div class="ra-table-wrap">
        <table class="ra-table">
          <thead><tr>
            <th>#</th><th>Matrícula</th><th>Nombre</th><th>Grupo</th>
            <th>Fecha</th><th>Hora</th><th>Estado</th><th>Incidencia</th><th>Turno</th><th>Acciones</th>
          </tr></thead>
          <tbody id="d-tbody"><tr><td colspan="10" class="td-placeholder">Usa los filtros y presiona <strong>Filtrar</strong></td></tr></tbody>
        </table>
        <div class="ra-table-footer">
          <span id="d-info">— registros</span>
          <span style="font-size:.75rem;">tabla <code>asistencia_diaria</code></span>
        </div>
      </div>
    </div>

  </div>

  <!-- ══════════ PANEL 3: WHATSAPP ══════════ -->
  <div id="panel-whatsapp" class="ra-panel">

    <!-- Configuración API -->
    <div class="ra-card" style="margin-bottom:1.4rem;">
      <div class="ra-card-header">
        <span class="ra-card-title">⚙️ Configuración de API</span>
        <div id="wa-estado-badge" class="turno-chip" style="<?= $waActivo ? 'background:#2e7d32;color:#fff;' : 'background:#c62828;color:#fff;' ?>">
          <?= $waActivo ? '● Activo' : '○ Inactivo' ?>
        </div>
      </div>
      <div class="ra-card-body">

        <div class="wa-config-row">
          <label class="ra-label">Activar notificaciones WhatsApp</label>
          <label class="wa-toggle">
            <input type="checkbox" id="wa-activo" <?= $waActivo ? 'checked' : '' ?> onchange="actualizarFormWA()">
            <span class="wa-toggle-slider"></span>
          </label>
        </div>

        <div class="wa-config-row" style="margin-top:.8rem;">
          <label class="ra-label">Proveedor</label>
          <select class="ra-input-filtro" id="wa-proveedor" onchange="actualizarFormWA()" style="max-width:220px;">
            <option value="callmebot"  <?= ($waConfig['proveedor']??'')==='callmebot'  ? 'selected':'' ?>>CallMeBot (Gratuito)</option>
            <option value="twilio"     <?= ($waConfig['proveedor']??'')==='twilio'     ? 'selected':'' ?>>Twilio</option>
            <option value="ultramsg"   <?= ($waConfig['proveedor']??'')==='ultramsg'   ? 'selected':'' ?>>UltraMsg</option>
            <option value="360dialog"  <?= ($waConfig['proveedor']??'')==='360dialog'  ? 'selected':'' ?>>360dialog</option>
            <option value="local_wa"   <?= ($waConfig['proveedor']??'')==='local_wa'   ? 'selected':'' ?>>⭐ WhatsApp Local (Gratis)</option>
          </select>
          <a id="wa-link-guia" href="#" target="_blank" style="font-size:.8rem;color:var(--cafe-medio);margin-left:.5rem;">📖 Guía de configuración</a>
        </div>

        <!-- CallMeBot fields -->
        <div id="wa-fields-callmebot" class="wa-fields">
          <div class="wa-config-row">
            <label class="ra-label">API Key de CallMeBot</label>
            <input type="text" id="wa-callmebot-apikey" class="ra-input-filtro" style="max-width:320px;"
                   value="<?= htmlspecialchars($waConfig['callmebot_apikey']??'') ?>"
                   placeholder="Tu API key (ej. 1234567)">
          </div>
          <p style="font-size:.78rem;color:var(--muted);margin:.3rem 0 0 0;">
            💡 Para obtener tu API key gratuita envía "I allow callmebot to send me messages" al +34 644 28 88 83 por WhatsApp
          </p>
        </div>

        <!-- Twilio fields -->
        <div id="wa-fields-twilio" class="wa-fields" style="display:none;">
          <div class="wa-config-row">
            <label class="ra-label">Account SID</label>
            <input type="text" id="wa-twilio-sid" class="ra-input-filtro" style="max-width:320px;"
                   value="<?= htmlspecialchars($waConfig['twilio_sid']??'') ?>" placeholder="ACxxxxxxxxxxxxxxxx">
          </div>
          <div class="wa-config-row">
            <label class="ra-label">Auth Token</label>
            <input type="password" id="wa-twilio-token" class="ra-input-filtro" style="max-width:320px;"
                   value="<?= htmlspecialchars($waConfig['twilio_token']??'') ?>">
          </div>
          <div class="wa-config-row">
            <label class="ra-label">Número origen (WhatsApp)</label>
            <input type="text" id="wa-twilio-from" class="ra-input-filtro" style="max-width:320px;"
                   value="<?= htmlspecialchars($waConfig['twilio_from']??'whatsapp:+14155238886') ?>">
          </div>
        </div>

        <!-- UltraMsg fields -->
        <div id="wa-fields-ultramsg" class="wa-fields" style="display:none;">
          <div class="wa-config-row">
            <label class="ra-label">Instance ID</label>
            <input type="text" id="wa-ultramsg-instance" class="ra-input-filtro" style="max-width:280px;"
                   value="<?= htmlspecialchars($waConfig['ultramsg_instance']??'') ?>">
          </div>
          <div class="wa-config-row">
            <label class="ra-label">Token</label>
            <input type="password" id="wa-ultramsg-token" class="ra-input-filtro" style="max-width:280px;"
                   value="<?= htmlspecialchars($waConfig['ultramsg_token']??'') ?>">
          </div>
        </div>

        <!-- 360dialog fields -->
        <div id="wa-fields-360dialog" class="wa-fields" style="display:none;">
          <div class="wa-config-row">
            <label class="ra-label">API Key</label>
            <input type="password" id="wa-dialog360-apikey" class="ra-input-filtro" style="max-width:320px;"
                   value="<?= htmlspecialchars($waConfig['dialog360_apikey']??'') ?>">
          </div>
        </div>

        <!-- WhatsApp Local fields -->
        <div id="wa-fields-local_wa" class="wa-fields" style="display:none;">
          <div style="padding:.8rem 1rem;background:#e8f5e9;border-radius:8px;border:1.5px solid #a5d6a7;">
            <strong style="color:#2e7d32;">⭐ Sin configuración requerida</strong>
            <p style="font-size:.82rem;color:#555;margin:.4rem 0 .6rem;">
              Solo necesitas tener Node.js instalado y el servidor corriendo.
              No requiere API keys ni cuentas de pago.
            </p>
            <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap;">
              <div id="wa-local-status" style="font-size:.82rem;color:#888;">Verificando…</div>
              <a href="wa-status.php" target="_blank" class="ra-btn-config" style="font-size:.8rem;">
                📱 Ver QR / Estado de conexión
              </a>
            </div>
          </div>
          <p style="font-size:.78rem;color:var(--muted);margin:.4rem 0 0;">
            Si es la primera vez: abre una terminal → entra a <code>wa-server/</code> → ejecuta <code>npm install</code> → <code>node server.js</code>
          </p>
        </div>

        <button class="ra-btn-config" style="margin-top:1.2rem;" onclick="guardarConfigWA()">
          💾 Guardar configuración
        </button>
      </div>
    </div>

    <!-- Plantillas de mensajes -->
    <div class="ra-card" style="margin-bottom:1.4rem;">
      <div class="ra-card-header">
        <span class="ra-card-title">📝 Plantillas de Mensajes</span>
      </div>
      <div class="ra-card-body">
        <p style="font-size:.8rem;color:var(--muted);margin-bottom:.8rem;">
          Variables disponibles: <code>{nombre}</code> <code>{apellidos}</code> <code>{fecha}</code> <code>{hora}</code> <code>{incidencia}</code> <code>{grupo}</code>
        </p>
        <div style="display:grid;gap:.8rem;">
          <div>
            <label class="ra-label">⚠️ Falta / Ausencia</label>
            <textarea id="wa-tpl-falta" class="ra-input-filtro" rows="2" style="width:100%;resize:vertical;"><?= htmlspecialchars($waConfig['plantilla_falta'] ?? 'Estimado padre de familia, le informamos que el alumno {nombre} no se presentó el día {fecha}. — CECYTEM') ?></textarea>
          </div>
          <div>
            <label class="ra-label">⏰ Tardanza</label>
            <textarea id="wa-tpl-tardanza" class="ra-input-filtro" rows="2" style="width:100%;resize:vertical;"><?= htmlspecialchars($waConfig['plantilla_tardanza'] ?? 'Estimado padre de familia, el alumno {nombre} llegó con tardanza el día {fecha} a las {hora}. — CECYTEM') ?></textarea>
          </div>
          <div>
            <label class="ra-label">🚨 Incidencia</label>
            <textarea id="wa-tpl-incidencia" class="ra-input-filtro" rows="2" style="width:100%;resize:vertical;"><?= htmlspecialchars($waConfig['plantilla_incidencia'] ?? 'Estimado padre de familia, el alumno {nombre} presentó la siguiente incidencia el {fecha}: {incidencia}. — CECYTEM') ?></textarea>
          </div>
        </div>
        <button class="ra-btn-config" style="margin-top:1rem;" onclick="guardarConfigWA()">💾 Guardar plantillas</button>
      </div>
    </div>

    <!-- Enviar notificación -->
    <div class="ra-card">
      <div class="ra-card-header">
        <span class="ra-card-title">📤 Enviar Notificación</span>
      </div>
      <div class="ra-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

          <!-- Columna izquierda: configuración del envío -->
          <div style="display:grid;gap:.8rem;">
            <div>
              <label class="ra-label">Tipo de envío</label>
              <div style="display:flex;gap:.5rem;margin-top:.3rem;">
                <button class="ra-lista-tab activa" id="btn-envio-individual" onclick="cambiarEnvio('individual',this)">👤 Individual</button>
                <button class="ra-lista-tab"        id="btn-envio-grupal"     onclick="cambiarEnvio('grupal',this)">👥 Grupal</button>
                <button class="ra-lista-tab"        id="btn-envio-faltantes"  onclick="cambiarEnvio('faltantes',this)">⚠️ Sin registrar hoy</button>
              </div>
            </div>

            <!-- Individual -->
            <div id="wa-envio-individual">
              <label class="ra-label">Matrícula del alumno</label>
              <div style="display:flex;gap:.5rem;">
                <input type="text" id="wa-buscar-alumno" class="ra-input-filtro" placeholder="Matrícula o nombre"
                       onkeydown="if(event.key==='Enter') buscarAlumnoWA()">
                <button class="ra-btn-filtrar" onclick="buscarAlumnoWA()">Buscar</button>
              </div>
              <div id="wa-alumno-resultado" style="margin-top:.5rem;font-size:.83rem;color:var(--muted);"></div>
            </div>

            <!-- Grupal -->
            <div id="wa-envio-grupal" style="display:none;">
              <label class="ra-label">Grupo</label>
              <select id="wa-grupo-sel" class="ra-input-filtro">
                <option value="">— Selecciona un grupo —</option>
                <?php foreach ($gruposWA as $g): ?>
                <option value="<?= $g['idGrupo'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Faltantes -->
            <div id="wa-envio-faltantes" style="display:none;">
              <p style="font-size:.83rem;color:var(--muted);">
                Se enviará notificación a todos los alumnos que aún <strong>no tienen registro hoy</strong> y tienen teléfono registrado.
              </p>
              <button class="ra-btn-filtrar" onclick="cargarFaltantesWA()" style="margin-top:.3rem;">🔄 Ver quiénes son</button>
              <div id="wa-faltantes-lista" style="margin-top:.5rem;font-size:.8rem;max-height:120px;overflow-y:auto;"></div>
            </div>

            <div>
              <label class="ra-label">Categoría</label>
              <select id="wa-categoria" class="ra-input-filtro" onchange="aplicarPlantilla()">
                <option value="falta">⚠️ Falta / Ausencia</option>
                <option value="tardanza">⏰ Tardanza</option>
                <option value="incidencia">🚨 Incidencia</option>
                <option value="personalizado">✏️ Mensaje personalizado</option>
              </select>
            </div>
          </div>

          <!-- Columna derecha: mensaje -->
          <div style="display:grid;gap:.8rem;align-content:start;">
            <div>
              <label class="ra-label">Mensaje a enviar</label>
              <textarea id="wa-mensaje" class="ra-input-filtro" rows="6" style="width:100%;resize:vertical;"
                        placeholder="El mensaje se generará automáticamente al seleccionar la categoría…"></textarea>
            </div>
            <div style="font-size:.78rem;color:var(--muted);">
              <span id="wa-char-count">0</span>/1000 caracteres
            </div>
            <button class="ra-btn-registrar" id="btn-enviar-wa"
                    onclick="enviarNotificacion()"
                    style="background:linear-gradient(135deg,#25d366,#128c7e);width:100%;padding:.8rem;">
              📱 Enviar por WhatsApp
            </button>
            <div id="wa-resultado" style="font-size:.83rem;text-align:center;min-height:1.2rem;"></div>
          </div>

        </div>
      </div>
    </div>

  </div>

</div><!-- /ra-main -->
</main>

<?php include_once "layout/footer_admin.php"; ?>

<!-- ══════════ MODAL: CONFIGURAR HORARIOS ══════════ -->
<div class="ra-modal-bg" id="modal-config-bg" onclick="if(event.target===this) cerrarConfig()">
  <div class="ra-modal">
    <div class="ra-modal-header">
      <span>⚙️ Configuración de Horarios</span>
      <button onclick="cerrarConfig()" class="ra-modal-close">✕</button>
    </div>
    <div class="ra-modal-body">
      <p style="font-size:.83rem;color:var(--muted);margin-bottom:1.2rem;">
        Define los turnos. El sistema detecta automáticamente si el alumno es
        <strong>Presente</strong> (llegó antes o en la hora límite) o <strong>Tardío</strong> (llegó después).
      </p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
        <!-- Matutino -->
        <div class="turno-box" id="box-matutino">
          <div class="turno-box-header">
            <label class="ra-toggle">
              <input type="checkbox" id="cfg-mat-activo" checked>
              <span class="toggle-track"></span>
            </label>
            <span class="turno-box-title">🌅 Turno Matutino</span>
          </div>
          <div class="turno-box-body">
            <label class="ra-label">Hora de inicio del turno</label>
            <input type="time" class="ra-input-filtro" id="cfg-mat-inicio" value="07:00" style="width:100%;margin-bottom:.7rem;">
            <label class="ra-label">Hora límite para ser "Presente"</label>
            <input type="time" class="ra-input-filtro" id="cfg-mat-limite" value="08:10" style="width:100%;">
            <p class="ra-hint" style="margin-top:.4rem;">Después de esta hora → Tardío</p>
          </div>
        </div>

        <!-- Vespertino -->
        <div class="turno-box" id="box-vespertino">
          <div class="turno-box-header">
            <label class="ra-toggle">
              <input type="checkbox" id="cfg-ves-activo" checked>
              <span class="toggle-track"></span>
            </label>
            <span class="turno-box-title">🌆 Turno Vespertino</span>
          </div>
          <div class="turno-box-body">
            <label class="ra-label">Hora de inicio del turno</label>
            <input type="time" class="ra-input-filtro" id="cfg-ves-inicio" value="13:00" style="width:100%;margin-bottom:.7rem;">
            <label class="ra-label">Hora límite para ser "Presente"</label>
            <input type="time" class="ra-input-filtro" id="cfg-ves-limite" value="14:10" style="width:100%;">
            <p class="ra-hint" style="margin-top:.4rem;">Después de esta hora → Tardío</p>
          </div>
        </div>
      </div>

      <!-- Preview en tiempo real -->
      <div class="config-preview" id="config-preview">
        <span id="config-preview-text">…</span>
      </div>
    </div>
    <div class="ra-modal-footer">
      <button class="ra-btn-cancelar" onclick="cerrarConfig()">Cancelar</button>
      <button class="ra-btn-guardar-cfg" onclick="guardarConfig()">💾 Guardar configuración</button>
    </div>
  </div>
</div>

<!-- ══════════ MODAL: EDITAR REGISTRO ══════════ -->
<div class="ra-modal-bg" id="modal-editar-bg" onclick="if(event.target===this) cerrarEditar()">
  <div class="ra-modal" style="max-width:460px;">
    <div class="ra-modal-header">
      <span id="modal-edit-titulo">✏️ Editar Registro</span>
      <button onclick="cerrarEditar()" class="ra-modal-close">✕</button>
    </div>
    <div class="ra-modal-body">
      <input type="hidden" id="edit-id">
      <input type="hidden" id="edit-tabla">

      <div id="edit-campos-diaria">
        <label class="ra-label">Estado</label>
        <select class="ra-input-filtro" id="edit-estado-diaria" style="width:100%;margin-bottom:.9rem;">
          <option value="Presente">✅ Presente</option>
          <option value="Tardío">⏰ Tardío</option>
        </select>
        <label class="ra-label">Hora de entrada</label>
        <input type="time" class="ra-input-filtro" id="edit-hora" style="width:100%;margin-bottom:.9rem;">
        <label class="ra-label">Incidencia</label>
        <input type="text" class="ra-input-filtro" id="edit-incidencia"
               placeholder="Ej: Sin uniforme, Olvido credencial… (vacío = sin incidencia)"
               style="width:100%;margin-bottom:.9rem;">
        <label class="ra-label">Observaciones (opcional)</label>
        <input type="text" class="ra-input-filtro" id="edit-obs" placeholder="Ej: Error de lector, corrección manual…" style="width:100%;">
      </div>

      <div id="edit-campos-materia" style="display:none;">
        <label class="ra-label">Estado</label>
        <select class="ra-input-filtro" id="edit-estado-materia" style="width:100%;">
          <option value="Ausente">❌ Ausente</option>
          <option value="Retardo">⏰ Retardo</option>
          <option value="Justificante">📄 Justificante</option>
        </select>
      </div>

      <div id="edit-info-alumno" style="margin-top:1rem;padding:.7rem 1rem;background:var(--crema);border-radius:8px;font-size:.82rem;color:var(--muted);"></div>
    </div>
    <div class="ra-modal-footer">
      <button class="ra-btn-eliminar-rec" onclick="confirmarEliminar()">🗑 Eliminar</button>
      <button class="ra-btn-cancelar"     onclick="cerrarEditar()">Cancelar</button>
      <button class="ra-btn-guardar-cfg"  onclick="guardarEdicion()">💾 Guardar cambios</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="ra-toast" id="ra-toast">
  <div class="t-icono" id="t-icono">✅</div>
  <div><div class="t-titulo" id="t-titulo">OK</div><div class="t-sub" id="t-sub"></div></div>
</div>

<style>
/* ── Botón config ── */
.ra-btn-config {
    background: var(--crema-card); border: 1.5px solid var(--borde);
    border-radius: var(--radius-sm); padding: .45rem .9rem;
    font-size: .83rem; font-weight: 600; color: var(--cafe-oscuro);
    cursor: pointer; transition: all .15s; font-family: 'Segoe UI', sans-serif;
    display:flex; align-items:center; gap:.35rem;
}
.ra-btn-config:hover { border-color: var(--cafe-medio); color: var(--cafe-medio); }

/* ── Turno chip ── */
.turno-chip {
    font-size:.72rem; font-weight:700; padding:.2rem .65rem;
    border-radius:20px; border:1px solid var(--borde);
    background: var(--crema); color: var(--muted);
}
.turno-chip.presente { background:var(--verde-bg); border-color:var(--verde-borde); color:var(--verde); }
.turno-chip.tardio   { background:var(--amarillo-bg); border-color:var(--amarillo-borde); color:var(--amarillo); }
.turno-chip.fuera    { background:var(--rojo-bg); border-color:var(--rojo-borde); color:var(--rojo); }

/* ── Preview de estado ── */
.estado-preview {
    display:flex; align-items:center; gap:.9rem;
    padding:.85rem 1.2rem; border-radius:var(--radius-sm);
    background: var(--verde-bg); border: 1.5px solid var(--verde-borde);
    margin-bottom:1rem; animation: fadeUp .25s ease;
}
.estado-preview.tardio { background:var(--amarillo-bg); border-color:var(--amarillo-borde); }
.estado-preview.fuera  { background:var(--rojo-bg); border-color:var(--rojo-borde); }
.estado-preview span:first-child { font-size:1.6rem; }

/* ── Botón registrar único ── */
.ra-btn-registrar {
    width:100%; padding:.9rem; border:none; border-radius:var(--radius-sm);
    background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
    color:#fff; font-size:1rem; font-weight:700; cursor:pointer;
    transition: all .18s; font-family:'Segoe UI',sans-serif;
    display:flex; align-items:center; justify-content:center; gap:.6rem;
    box-shadow: 0 3px 12px rgba(139,69,19,.25);
}
.ra-btn-registrar:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 20px rgba(139,69,19,.35); }
.ra-btn-registrar:disabled { opacity:.35; cursor:not-allowed; transform:none; }
.ra-btn-registrar.tardio { background: linear-gradient(135deg, #c77c00, #e09c20); }
.ra-btn-registrar.fuera  { background: linear-gradient(135deg, #b71c1c, #c62828); }

/* ── Sub-tabs ── */
.ra-subtabs { display:flex; gap:.5rem; margin-bottom:1.2rem; }
.ra-subtab {
    padding:.55rem 1.2rem; background:var(--crema-card);
    border:1.5px solid var(--borde); border-radius:var(--radius-sm);
    font-family:'Segoe UI',sans-serif; font-size:.86rem; font-weight:600;
    color:var(--muted); cursor:pointer; transition:all .15s;
}
.ra-subtab:hover  { color:var(--cafe-medio); border-color:var(--cafe-medio); }
.ra-subtab.activa { background:rgba(139,69,19,.1); border-color:var(--cafe-medio); color:var(--cafe-medio); }

/* ── Acciones en tabla ── */
.btn-accion-tabla {
    padding:.22rem .55rem; border-radius:5px; border:1px solid;
    font-size:.72rem; font-weight:700; cursor:pointer;
    transition:all .13s; font-family:'Segoe UI',sans-serif;
    display:inline-flex; align-items:center; gap:.25rem;
}
.btn-editar   { background:var(--azul-bg);  border-color:var(--azul-borde);  color:var(--azul); }
.btn-editar:hover { background:var(--azul); color:#fff; }
.td-placeholder { text-align:center; padding:2rem; color:var(--muted); }

/* ── Modales ── */
.ra-modal-bg {
    position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:9000; display:none; align-items:center; justify-content:center;
    backdrop-filter:blur(3px); padding:1rem;
}
.ra-modal-bg.activo { display:flex; animation: fadeUp .22s ease; }
.ra-modal {
    background:var(--crema-card); border-radius:var(--radius);
    box-shadow:0 20px 60px rgba(0,0,0,.3); width:100%; max-width:680px;
    overflow:hidden;
}
.ra-modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1rem 1.4rem; background:rgba(240,232,220,.6);
    border-bottom:1px solid var(--borde);
    font-weight:700; font-size:.95rem; color:var(--cafe-oscuro);
}
.ra-modal-close {
    background:none; border:none; font-size:1.1rem; cursor:pointer;
    color:var(--muted); padding:.2rem .5rem; border-radius:4px;
    transition:all .13s;
}
.ra-modal-close:hover { background:var(--rojo-bg); color:var(--rojo); }
.ra-modal-body   { padding:1.4rem; }
.ra-modal-footer {
    padding:.9rem 1.4rem; background:rgba(240,232,220,.4);
    border-top:1px solid var(--borde);
    display:flex; justify-content:flex-end; gap:.6rem;
}
.ra-btn-cancelar {
    padding:.55rem 1.1rem; background:var(--crema-card);
    border:1px solid var(--borde); border-radius:var(--radius-sm);
    font-family:'Segoe UI',sans-serif; font-size:.85rem; font-weight:600;
    color:var(--muted); cursor:pointer;
}
.ra-btn-cancelar:hover { border-color:var(--cafe-medio); color:var(--cafe-medio); }
.ra-btn-guardar-cfg {
    padding:.55rem 1.3rem; background:var(--cafe-medio); border:none;
    border-radius:var(--radius-sm); color:#fff; font-family:'Segoe UI',sans-serif;
    font-size:.85rem; font-weight:700; cursor:pointer; transition:all .15s;
}
.ra-btn-guardar-cfg:hover { background:var(--cafe-claro); }
.ra-btn-eliminar-rec {
    padding:.55rem 1rem; background:var(--rojo-bg);
    border:1px solid var(--rojo-borde); border-radius:var(--radius-sm);
    color:var(--rojo); font-family:'Segoe UI',sans-serif;
    font-size:.85rem; font-weight:600; cursor:pointer; margin-right:auto;
    transition:all .15s;
}
.ra-btn-eliminar-rec:hover { background:var(--rojo); color:#fff; }

/* ── Turno boxes config ── */
.turno-box {
    border:1.5px solid var(--borde); border-radius:var(--radius-sm);
    overflow:hidden;
}
.turno-box-header {
    display:flex; align-items:center; gap:.7rem;
    padding:.75rem 1rem; background:rgba(240,232,220,.5);
    border-bottom:1px solid var(--borde);
}
.turno-box-title { font-weight:700; font-size:.88rem; color:var(--cafe-oscuro); }
.turno-box-body  { padding:.9rem 1rem; }

/* ── Toggle switch ── */
.ra-toggle { position:relative; display:inline-flex; cursor:pointer; }
.ra-toggle input { opacity:0; width:0; height:0; }
.toggle-track {
    display:block; width:38px; height:20px;
    background:var(--borde); border-radius:20px;
    transition:background .2s;
    position:relative;
}
.toggle-track::after {
    content:''; position:absolute; top:3px; left:3px;
    width:14px; height:14px; border-radius:50%;
    background:#fff; transition:transform .2s;
}
.ra-toggle input:checked + .toggle-track { background:var(--cafe-medio); }
.ra-toggle input:checked + .toggle-track::after { transform:translateX(18px); }

/* ── Config preview ── */
.config-preview {
    margin-top:1.2rem; padding:.8rem 1.1rem;
    background:var(--crema); border-radius:var(--radius-sm);
    border:1px solid var(--borde); font-size:.82rem; color:var(--cafe-oscuro);
    min-height:40px;
}

body.dark-mode .ra-modal { background:#1e1a18; }
body.dark-mode .ra-modal-header { background:#252018; }
body.dark-mode .ra-modal-footer { background:#1a1510; }
body.dark-mode .turno-box { border-color:#3a3028; }
body.dark-mode .turno-box-header { background:#201c18; }
body.dark-mode .config-preview { background:#1a1510; border-color:#3a3028; }

/* ══════════════════════════════════════
   MEDIA QUERIES — elementos inline
   ══════════════════════════════════════ */

/* Tablet: 768px */
@media (max-width: 768px) {
    /* Cabecera: botón config + badge */
    .ra-btn-config { padding: .38rem .65rem; font-size: .78rem; }

    /* Chip de turno: abreviar texto */
    .turno-chip { font-size: .65rem; padding: .18rem .45rem; max-width: 160px;
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Preview estado */
    .estado-preview { padding: .65rem .9rem; gap: .6rem; }
    .estado-preview span:first-child { font-size: 1.3rem; }

    /* Modal de configuración: turnos en columna */
    .ra-modal-body > div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    .ra-modal-footer { flex-wrap: wrap; gap: .4rem; }
    .ra-modal-footer button { flex: 1 1 calc(50% - .2rem); }
    .ra-btn-eliminar-rec { flex: 1 1 100% !important; }

    /* Turno boxes */
    .turno-box-header { padding: .6rem .8rem; }
    .turno-box-body   { padding: .7rem .8rem; }

    /* Botón registrar */
    .ra-btn-registrar { font-size: .92rem; padding: .8rem; }
}

/* Móvil: 480px */
@media (max-width: 480px) {
    /* Cabecera: ocultar texto del botón config */
    .ra-btn-config { gap: 0; }

    /* Turno chip: muy compacto */
    .turno-chip { font-size: .6rem; max-width: 110px; }

    /* Preview estado: compacto */
    .estado-preview { padding: .55rem .75rem; font-size: .82rem; }

    /* Modal: pantalla completa */
    .ra-modal { max-width: 100vw; border-radius: 12px 12px 0 0;
                position: fixed; bottom: 0; margin: 0; }
    .ra-modal-bg { align-items: flex-end; }
    .ra-modal-header { padding: .75rem 1rem; }
    .ra-modal-body   { padding: .9rem; max-height: 70vh; overflow-y: auto; }

    /* Botón registrar */
    .ra-btn-registrar { font-size: .85rem; padding: .75rem; }

    /* Subtabs */
    .ra-subtabs { flex-wrap: wrap; }
    .ra-subtab  { flex: 1 1 calc(50% - .25rem); text-align: center;
                  font-size: .78rem; padding: .4rem .5rem; }

    /* Acciones en tabla */
    .btn-accion-tabla { font-size: .68rem; padding: .18rem .4rem; }
}

/* Móvil muy pequeño: 360px */
@media (max-width: 360px) {
    .turno-chip { display: none; }
    .estado-preview { font-size: .78rem; }
    .ra-btn-registrar { font-size: .8rem; }
}

/* ── Modo incidencia banner ── */
.ra-modo-banner {
    display:flex; align-items:center; gap:.8rem;
    padding:.85rem 1.3rem; border-radius:var(--radius-sm);
    background: linear-gradient(135deg, #c62828, #e53935);
    color:#fff; font-size:.9rem; margin-bottom:1rem;
    box-shadow:0 3px 14px rgba(198,40,40,.35);
    animation: fadeUp .3s ease;
}
.ra-modo-icono { font-size:1.5rem; }
.ra-modo-badge {
    background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.4);
    border-radius:20px; padding:.2rem .8rem; font-size:.78rem; font-weight:700;
    white-space:nowrap;
}

/* ── Pill incidencia ── */
.pill-incidencia {
    display:inline-block; padding:.15rem .6rem; border-radius:20px;
    font-size:.72rem; font-weight:700; background:#fff3e0;
    border:1px solid #ffb74d; color:#e65100; white-space:nowrap;
    max-width:140px; overflow:hidden; text-overflow:ellipsis;
}

/* ── WA dot ── */
.wa-dot-activo { color:#25d366; font-size:.7rem; }

/* ── WA toggle ── */
.wa-toggle { position:relative; display:inline-flex; cursor:pointer; align-items:center; gap:.5rem; }
.wa-toggle input { opacity:0; width:0; height:0; position:absolute; }
.wa-toggle-slider {
    display:block; width:44px; height:24px;
    background:#ccc; border-radius:24px; transition:background .2s; position:relative;
}
.wa-toggle-slider::after {
    content:''; position:absolute; top:3px; left:3px;
    width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s;
}
.wa-toggle input:checked + .wa-toggle-slider { background:#25d366; }
.wa-toggle input:checked + .wa-toggle-slider::after { transform:translateX(20px); }

/* ── WA config rows ── */
.wa-config-row { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:.5rem; }
.wa-config-row .ra-label { min-width:200px; margin:0; }
.wa-fields { margin-top:.8rem; padding-top:.8rem; border-top:1px solid var(--borde); display:grid; gap:.5rem; }

@media (max-width:768px) {
    .ra-modo-banner { flex-wrap:wrap; font-size:.8rem; }
    .wa-config-row { flex-direction:column; align-items:flex-start; gap:.3rem; }
    .wa-config-row .ra-label { min-width:unset; }
}
</style>

<script>
// ── Estado global ──
let alumnoActual  = null;
let registrosHoy  = [];
let tablaDataD    = [];
let configHorarios = null;
let estadoAutoDetectado = 'Presente';

// ── Modo incidencia (leído desde PHP) ──
const INCIDENCIA_ACTIVA = <?= json_encode($incidenciaActiva) ?>;
const WA_ACTIVO         = <?= json_encode($waActivo) ?>;
const WA_CONFIG         = <?= json_encode($waConfig) ?>;
// Alumnos encontrados en búsqueda WA
let waAlumnoActual = null;
let waFaltantesHoy = [];

// ── Reloj + detección continua de turno ──
setInterval(() => {
    const n = new Date();
    document.getElementById('reloj-live').textContent =
        n.toLocaleDateString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric'}) +
        '  ' + n.toLocaleTimeString('es-MX');
    if (configHorarios) actualizarChipTurno();
}, 1000);

// ── Cargar config + registros del día al iniciar ──
(function init() {
    fetch('ajax/obtenerConfig.php')
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                configHorarios = data.config;
                actualizarChipTurno();
                poblarFormConfig();
            }
        }).catch(() => {});

    // Poblar registrosHoy desde BD: Tardíos y Sin registrar funcionan
    // aunque la página se haya recargado
    const _h = new Date();
    const _f = _h.getFullYear()+'-'+String(_h.getMonth()+1).padStart(2,'0')+'-'+String(_h.getDate()).padStart(2,'0');
    fetch('ajax/obtenerRegistros.php?tipo=diaria&fecha='+_f)
        .then(r => r.json())
        .then(data => {
            if (data.registros) {
                registrosHoy = data.registros.map(r => ({
                    matricula: r.matricula,
                    nombre:    r.apellidos + ' ' + r.nombre,
                    hora:      r.hora_entrada ? r.hora_entrada.substr(0,5) : '—',
                    estado:    r.estado,
                    turno:     (r.dispositivo||'').split('|').pop().trim(),
                }));
                renderHoyLista();
                actualizarStatsHoy();
            }
        }).catch(() => {});

    document.getElementById('inp-matricula').focus();
    cargarFaltantesSilencioso();
})();

// ── Detectar turno activo y estado según hora actual ──
function detectarTurno(horaHM) {
    if (!configHorarios) return {turno:'Sin configuración', estado:'Presente', clase:'fuera'};
    for (const key of ['matutino','vespertino']) {
        const t = configHorarios[key];
        if (!t?.activo) continue;
        const fin = padH(String(parseInt(t.horaLimite.split(':')[0])+2)) + ':' + t.horaLimite.split(':')[1];
        if (horaHM >= t.horaInicio && horaHM <= fin) {
            const esTardio = horaHM > t.horaLimite;
            return {
                turno: t.nombre || key,
                estado: esTardio ? 'Tardío' : 'Presente',
                clase: esTardio ? 'tardio' : 'presente',
                limite: t.horaLimite,
            };
        }
    }
    return {turno:'Fuera de horario', estado:'Presente', clase:'fuera'};
}
function padH(h) { return h.length < 2 ? '0'+h : h; }

function actualizarChipTurno() {
    const ahora = new Date();
    const hm = ahora.toTimeString().substr(0,5);
    const det = detectarTurno(hm);
    estadoAutoDetectado = det.estado;

    const chip = document.getElementById('turno-chip');
    chip.className = 'turno-chip ' + det.clase;
    chip.textContent = det.turno + ' · ' + (det.clase === 'presente' ? '✅ Presente' : det.clase === 'tardio' ? '⏰ Tardío' : '⛔ Fuera de horario');

    const label = document.getElementById('turno-activo-label');
    if (det.clase === 'fuera') {
        label.textContent = 'Fuera de horario configurado — se registrará como Presente';
    } else if (det.clase === 'tardio') {
        label.textContent = 'Turno ' + det.turno + ' · Límite: ' + det.limite + ' · Los alumnos serán marcados como Tardíos';
    } else {
        label.textContent = 'Turno ' + det.turno + ' · Límite: ' + det.limite + ' · Los alumnos serán marcados como Presentes';
    }

    // Actualizar preview y botón si hay alumno cargado
    actualizarPreviewEstado(det);
    if (alumnoActual) {
        actualizarBotonRegistrar(det);
    }
}

function actualizarPreviewEstado(det) {
    const prev = document.getElementById('estado-preview');
    if (!alumnoActual) { prev.style.display='none'; return; }
    prev.style.display = 'flex';
    prev.className = 'estado-preview ' + (det.clase !== 'presente' ? det.clase : '');
    document.getElementById('estado-preview-icon').textContent = det.clase==='tardio' ? '⏰' : det.clase==='fuera' ? '⛔' : '✅';
    document.getElementById('estado-preview-text').textContent = 'Se registrará como: ' + det.estado;
    document.getElementById('estado-preview-sub').textContent  =
        det.clase==='presente' ? 'Dentro del horario permitido' :
        det.clase==='tardio'   ? 'Pasó la hora límite del turno ' + det.turno :
                                 'Fuera del horario — se registra como Presente';
}

function actualizarBotonRegistrar(det) {
    const btn = document.getElementById('btn-registrar');
    btn.className = 'ra-btn-registrar' + (det.clase !== 'presente' ? ' ' + det.clase : '');
    document.getElementById('btn-icon').textContent = det.clase==='tardio' ? '⏰' : det.clase==='fuera' ? '⛔' : '✅';
    document.getElementById('btn-text').textContent =
        det.clase==='tardio' ? 'Registrar como Tardío' :
        det.clase==='fuera'  ? 'Registrar (fuera de horario)' : 'Registrar como Presente';
}

// ── Tabs / Subtabs ──
function cambiarTab(panel, btn) {
    document.querySelectorAll('.ra-panel').forEach(p=>p.classList.remove('activo'));
    document.querySelectorAll('.ra-tab').forEach(b=>b.classList.remove('activa'));
    document.getElementById('panel-'+panel).classList.add('activo');
    btn.classList.add('activa');
    if (panel==='control')   iniciarControl();
    if (panel==='whatsapp')  iniciarWhatsApp();
}
function iniciarControl() {
    const hoy = new Date();
    const fechaLocal = hoy.getFullYear() + '-' +
        String(hoy.getMonth()+1).padStart(2,'0') + '-' +
        String(hoy.getDate()).padStart(2,'0');
    document.getElementById('d-fecha').value = fechaLocal;
    cargarDiaria();
    cargarGruposSelect();
}

// ── Toast ──
function toast(tipo,titulo,sub) {
    const t = document.getElementById('ra-toast');
    const ic = {ok:'✅',warn:'⏰',error:'❌',info:'ℹ️'};
    t.className = 'ra-toast mostrar t-'+tipo;
    document.getElementById('t-icono').textContent  = ic[tipo]||'✅';
    document.getElementById('t-titulo').textContent = titulo;
    document.getElementById('t-sub').textContent    = sub;
    clearTimeout(t._t);
    t._t = setTimeout(()=>t.classList.remove('mostrar'), 3800);
}

// ── Helpers ──
function iniciales(n,a) { return ((n?.[0]||'')+(a?.[0]||'')).toUpperCase(); }
function pillD(e) {
    const m={'Presente':'pe-presente','Tardío':'pe-retardo'};
    return `<span class="pill-estado ${m[e]||'pe-presente'}">${e}</span>`;
}
function pillM(e) {
    const m={'Ausente':'pe-ausente','Retardo':'pe-retardo','Justificante':'pe-justificante'};
    return `<span class="pill-estado ${m[e]||'pe-ausente'}">${e}</span>`;
}
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ── Buscar alumno ──
function buscarAlumno() {
    const mat = document.getElementById('inp-matricula').value.trim();
    if (!mat) { toast('error','Campo vacío','Ingresa una matrícula'); return; }
    document.getElementById('ra-empty').style.display='none';
    document.getElementById('ra-alumno-card').classList.remove('visible');
    document.getElementById('estado-preview').style.display='none';
    document.getElementById('ra-loader').classList.add('visible');

    fetch('ajax/buscarAlumno.php?matricula='+encodeURIComponent(mat))
        .then(r=>r.json())
        .then(data=>{
            document.getElementById('ra-loader').classList.remove('visible');
            if (data.error) {
                toast('error','No encontrado',data.error);
                document.getElementById('ra-empty').style.display='';
                alumnoActual=null; return;
            }
            mostrarAlumno(data);

            // ── AUTO-REGISTRO al escanear ──
            // Si ya está registrado hoy solo mostramos su info, sin duplicar
            if (data.registroHoy) {
                toast('warn','Ya registrado hoy',
                    data.alumno.apellidos+', '+data.alumno.nombre+
                    ' — '+data.registroHoy.estado+' a las '+
                    (data.registroHoy.hora_entrada?.substr(0,5)||''));
                // Limpiar input rápido para siguiente alumno
                setTimeout(()=>{
                    document.getElementById('inp-matricula').value='';
                    document.getElementById('inp-matricula').focus();
                    document.getElementById('ra-alumno-card').classList.remove('visible');
                    document.getElementById('estado-preview').style.display='none';
                    document.getElementById('ra-empty').style.display='';
                    alumnoActual=null;
                }, 3000);
            } else {
                // No registrado → registrar automáticamente
                registrar();
            }
        })
        .catch(()=>{
            document.getElementById('ra-loader').classList.remove('visible');
            document.getElementById('ra-empty').style.display='';
            toast('error','Error de conexión','No se pudo contactar al servidor');
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
        yaTag.textContent = '✓ ' + data.registroHoy.estado + ' — ' + (data.registroHoy.hora_entrada?.substr(0,5)||'');
    }

    const hl = document.getElementById('ra-hist-list');
    hl.innerHTML = data.historial?.length
        ? data.historial.map(h=>`
            <div class="ra-hist-item">
                <span class="ra-hist-fecha">${h.fecha}</span>
                <span class="ra-hist-hora">${h.hora_entrada?h.hora_entrada.substr(0,5):''}</span>
                ${pillD(h.estado)}
            </div>`).join('')
        : '<div style="font-size:.8rem;color:var(--muted);text-align:center;padding:.5rem;">Sin registros previos</div>';

    document.getElementById('ra-alumno-card').classList.add('visible');

    // Preview visual del estado que se asignará
    const hm = new Date().toTimeString().substr(0,5);
    const det = detectarTurno(hm);
    if (!yaReg) {
        actualizarPreviewEstado(det);
        actualizarBotonRegistrar(det);
    }
}

function setBotones(dis) {
    document.getElementById('btn-registrar').disabled = dis;
}

// ── Registrar (estado automático desde servidor) ──
function registrar() {
    if (!alumnoActual) return;
    setBotones(true);

    fetch('ajax/guardarAsistenciaDiaria.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            id_alumno:  alumnoActual.id_alumno,
            dispositivo:'Manual',
            incidencia: INCIDENCIA_ACTIVA || null,
        })
    })
    .then(r=>r.json())
    .then(data=>{
        if (!data.ok) { toast('error','Error al guardar',data.msg||''); setBotones(false); return; }

        const yaTag = document.getElementById('ra-ya-tag');
        yaTag.textContent = '✓ ' + data.estado + ' — ' + data.hora.substr(0,5)
            + (data.incidencia ? ' ⚠️ '+data.incidencia : '');
        yaTag.classList.add('visible');

        registrosHoy.unshift({
            matricula:  alumnoActual.matricula,
            nombre:     alumnoActual.apellidos+' '+alumnoActual.nombre,
            hora:       data.hora.substr(0,5),
            estado:     data.estado,
            turno:      data.turno||'—',
            incidencia: data.incidencia||'',
        });
        renderHoyLista();
        actualizarStatsHoy();

        const msgInc = data.incidencia ? ' | ⚠️ '+data.incidencia : '';
        toast(data.estado==='Tardío'?'warn':'ok',
            data.estado+' registrado ('+data.turno+')',
            alumnoActual.apellidos+', '+alumnoActual.nombre+' — '+data.hora.substr(0,5)+msgInc);

        setTimeout(()=>{
            document.getElementById('inp-matricula').value='';
            document.getElementById('inp-matricula').focus();
            document.getElementById('ra-alumno-card').classList.remove('visible');
            document.getElementById('estado-preview').style.display='none';
            document.getElementById('ra-empty').style.display='';
            alumnoActual=null;
        }, 2200);
    })
    .catch(()=>{ toast('error','Error de conexión',''); setBotones(false); });
}

// ── Lista de hoy: tab activa ──
let listaTabActual = 'todos';

function cambiarListaHoy(tab, btn) {
    listaTabActual = tab;
    ['todos','tardios','faltantes'].forEach(t => {
        document.getElementById('lista-'+t).style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('.ra-lista-tab').forEach(b => b.classList.remove('activa'));
    if (btn) btn.classList.add('activa');
    // Al abrir faltantes, recargar siempre para tener datos frescos
    if (tab === 'faltantes') cargarFaltantes();
    // Actualizar contador en footer
    actualizarContadorLista(tab);
}

function actualizarContadorLista(tab) {
    const cnt = document.getElementById('hoy-count');
    if (tab === 'todos')     cnt.textContent = registrosHoy.length + ' entradas';
    else if (tab === 'tardios')  cnt.textContent = registrosHoy.filter(r=>r.estado==='Tardío').length + ' tardíos';
    else if (tab === 'faltantes') cnt.textContent = (window._faltantesCount||0) + ' sin registrar';
}

function renderHoyLista() {
    // ── Tab Todos ──
    const elTodos = document.getElementById('lista-todos');
    elTodos.innerHTML = registrosHoy.length
        ? registrosHoy.map(r=>`
            <div class="ra-hoy-item">
                <span class="ra-hoy-mat">${r.matricula}</span>
                <span class="ra-hoy-nombre">${r.nombre}</span>
                <span class="ra-hoy-hora">${r.hora}</span>
                ${pillD(r.estado)}
            </div>`).join('')
        : '<div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Sin registros aún hoy</p></div>';

    // ── Tab Tardíos ──
    const tardios = registrosHoy.filter(r => r.estado === 'Tardío');
    const elTardios = document.getElementById('lista-tardios');
    elTardios.innerHTML = tardios.length
        ? tardios.map(r=>`
            <div class="ra-hoy-item ra-hoy-tardio">
                <span class="ra-hoy-mat">${r.matricula}</span>
                <span class="ra-hoy-nombre">${r.nombre}</span>
                <span class="ra-hoy-hora v-amarillo">${r.hora}</span>
                ${pillD(r.estado)}
            </div>`).join('')
        : '<div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>Sin tardíos registrados hoy ✅</p></div>';

    actualizarContadorLista(listaTabActual);
}

function cargarFaltantes() {
    const el = document.getElementById('lista-faltantes');
    el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:2rem;color:var(--muted);font-size:.85rem;"><div class="ra-spinner"></div> Cargando faltantes…</div>';

    fetch('ajax/obtenerFaltantes.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                el.innerHTML = `<div style="padding:1.5rem;text-align:center;color:var(--rojo);">${data.error}</div>`;
                return;
            }
            window._faltantesCount = data.faltantes.length;
            document.getElementById('dia-sin').textContent = data.faltantes.length;
            actualizarContadorLista('faltantes');

            el.innerHTML = data.faltantes.length
                ? data.faltantes.map(a=>`
                    <div class="ra-hoy-item ra-hoy-faltante">
                        <span class="ra-hoy-mat">${esc(a.matricula)}</span>
                        <span class="ra-hoy-nombre">${esc(a.apellidos+' '+a.nombre)}</span>
                        <span class="ra-badge rb-grupo" style="font-size:.7rem;">${esc(a.nombre_grupo||'—')}</span>
                        <span class="pill-estado pe-ausente">Sin registrar</span>
                    </div>`).join('')
                : '<div class="ra-empty" style="padding:1.5rem;border:none;background:none;"><p>¡Todos los alumnos están registrados hoy! 🎉</p></div>';
        })
        .catch(() => {
            el.innerHTML = '<div style="padding:1.5rem;text-align:center;color:var(--rojo);">Error de conexión</div>';
        });
}

function actualizarStatsHoy() {
    document.getElementById('dia-total').textContent     = registrosHoy.length;
    document.getElementById('dia-presentes').textContent = registrosHoy.filter(r=>r.estado==='Presente').length;
    document.getElementById('dia-tardios').textContent   = registrosHoy.filter(r=>r.estado==='Tardío').length;
    // Faltantes: se actualiza al abrir esa tab, o después de cada registro
    cargarFaltantesSilencioso();
}

function cargarFaltantesSilencioso() {
    fetch('ajax/obtenerFaltantes.php')
        .then(r=>r.json())
        .then(data=>{
            if (data.ok) {
                window._faltantesCount = data.faltantes.length;
                document.getElementById('dia-sin').textContent = data.faltantes.length;
                // Si la tab faltantes está activa, re-renderizar
                if (listaTabActual === 'faltantes') cargarFaltantes();
            }
        }).catch(()=>{});
}

// ══════════════════════════════════
// MODAL CONFIGURACIÓN
// ══════════════════════════════════
function abrirConfig() {
    poblarFormConfig();
    document.getElementById('modal-config-bg').classList.add('activo');
    actualizarPreviewConfig();
}
function cerrarConfig() {
    document.getElementById('modal-config-bg').classList.remove('activo');
}
function poblarFormConfig() {
    if (!configHorarios) return;
    const m = configHorarios.matutino, v = configHorarios.vespertino;
    document.getElementById('cfg-mat-activo').checked  = m?.activo ?? true;
    document.getElementById('cfg-mat-inicio').value    = m?.horaInicio || '07:00';
    document.getElementById('cfg-mat-limite').value    = m?.horaLimite || '08:10';
    document.getElementById('cfg-ves-activo').checked  = v?.activo ?? true;
    document.getElementById('cfg-ves-inicio').value    = v?.horaInicio || '13:00';
    document.getElementById('cfg-ves-limite').value    = v?.horaLimite || '14:10';
    actualizarPreviewConfig();
}
function actualizarPreviewConfig() {
    const matAct = document.getElementById('cfg-mat-activo')?.checked;
    const matIni = document.getElementById('cfg-mat-inicio')?.value;
    const matLim = document.getElementById('cfg-mat-limite')?.value;
    const vesAct = document.getElementById('cfg-ves-activo')?.checked;
    const vesIni = document.getElementById('cfg-ves-inicio')?.value;
    const vesLim = document.getElementById('cfg-ves-limite')?.value;

    const hm = new Date().toTimeString().substr(0,5);
    let resumen = `🕐 Hora actual del servidor: <strong>${hm}</strong><br>`;

    if (matAct) {
        const tard = hm > matLim && hm >= matIni;
        const dentro = hm >= matIni && hm <= (padH(String(parseInt(matLim.split(':')[0])+2))+':'+matLim.split(':')[1]);
        resumen += `🌅 Matutino ${matIni}–${matLim}: ahora serías <strong>${dentro ? (tard?'Tardío':'Presente') : '(fuera de rango)'}</strong><br>`;
    }
    if (vesAct) {
        const tard = hm > vesLim && hm >= vesIni;
        const dentro = hm >= vesIni && hm <= (padH(String(parseInt(vesLim.split(':')[0])+2))+':'+vesLim.split(':')[1]);
        resumen += `🌆 Vespertino ${vesIni}–${vesLim}: ahora serías <strong>${dentro ? (tard?'Tardío':'Presente') : '(fuera de rango)'}</strong>`;
    }
    const el = document.getElementById('config-preview');
    if (el) el.innerHTML = resumen;
}

// Listeners en tiempo real para el preview
['cfg-mat-activo','cfg-mat-inicio','cfg-mat-limite','cfg-ves-activo','cfg-ves-inicio','cfg-ves-limite']
    .forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', actualizarPreviewConfig);
    });

function guardarConfig() {
    const config = {
        matutino: {
            activo:     document.getElementById('cfg-mat-activo').checked,
            horaInicio: document.getElementById('cfg-mat-inicio').value,
            horaLimite: document.getElementById('cfg-mat-limite').value,
            nombre:     'Matutino',
        },
        vespertino: {
            activo:     document.getElementById('cfg-ves-activo').checked,
            horaInicio: document.getElementById('cfg-ves-inicio').value,
            horaLimite: document.getElementById('cfg-ves-limite').value,
            nombre:     'Vespertino',
        },
    };

    fetch('ajax/guardarConfig.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(config)
    })
    .then(r=>r.json())
    .then(data=>{
        if (!data.ok) { toast('error','Error al guardar',data.msg); return; }
        configHorarios = config;
        cerrarConfig();
        actualizarChipTurno();
        toast('ok','Configuración guardada','Los horarios se aplicarán al siguiente registro');
    })
    .catch(()=>toast('error','Error de conexión',''));
}

// ══════════════════════════════════
// CONTROL: CARGAR DATOS
// ══════════════════════════════════
function cargarGruposSelect() {
    const _h = new Date(), _f = _h.getFullYear()+'-'+String(_h.getMonth()+1).padStart(2,'0')+'-'+String(_h.getDate()).padStart(2,'0');
    fetch('ajax/obtenerRegistros.php?tipo=diaria&fecha='+_f)
        .then(r=>r.json())
        .then(data=>{
            ['d-grupo'].forEach(id=>{
                const sel=document.getElementById(id);
                while(sel.options.length>1) sel.remove(1);
                (data.grupos||[]).forEach(g=>sel.add(new Option(g.nombre,g.idGrupo)));
            });
            // Poblar filtro de incidencias con los tipos usados en BD
            const selInc = document.getElementById('d-incidencia');
            while(selInc.options.length>2) selInc.remove(2); // conservar "Todas" y "Sin incidencia"
            (data.tiposIncidencia||[]).forEach(inc=>{
                selInc.add(new Option('⚠️ '+inc, inc));
            });
        }).catch(()=>{});
}

function cargarDiaria() {
    const p = new URLSearchParams({
        tipo:       'diaria',
        buscar:     document.getElementById('d-buscar').value.trim(),
        fecha:      document.getElementById('d-fecha').value,
        grupo:      document.getElementById('d-grupo').value,
        estado:     document.getElementById('d-estado').value,
        incidencia: document.getElementById('d-incidencia').value,
    });
    setLoading('d-tbody', 10);
    fetch('ajax/obtenerRegistros.php?'+p)
        .then(r=>r.json())
        .then(data=>{
            if(data.error){setError('d-tbody',10,data.error);return;}
            tablaDataD = data.registros||[];
            const tbody = document.getElementById('d-tbody');
            tbody.innerHTML = tablaDataD.length
                ? tablaDataD.map((r,i)=>`
                    <tr>
                        <td style="color:var(--muted);font-size:.74rem;">${i+1}</td>
                        <td class="td-mat">${esc(r.matricula)}</td>
                        <td>${esc(r.apellidos+' '+r.nombre)}</td>
                        <td style="font-size:.8rem;">${esc(r.grupo||'—')}</td>
                        <td class="td-hora">${esc(r.fecha)}</td>
                        <td class="td-hora">${r.hora_entrada?esc(r.hora_entrada.substr(0,5)):'—'}</td>
                        <td>${pillD(r.estado)}</td>
                        <td>${r.incidencia ? `<span class="pill-incidencia" title="${esc(r.incidencia)}">⚠️ ${esc(r.incidencia)}</span>` : '<span style="color:var(--muted);font-size:.74rem;">—</span>'}</td>
                        <td style="font-size:.74rem;color:var(--muted);">${esc((r.dispositivo||'').split('|').pop().trim())}</td>
                        <td>
                          <button class="btn-accion-tabla btn-editar"
                            onclick="abrirEditar('diaria',${r.id_asistencia_diaria},'${esc(r.apellidos+' '+r.nombre)}','${r.estado}','${r.hora_entrada?.substr(0,5)||''}','${esc(r.incidencia||'')}')">
                            ✏️ Editar
                          </button>
                        </td>
                    </tr>`).join('')
                : '<tr><td colspan="10" class="td-placeholder">Sin registros encontrados</td></tr>';

            const s = data.stats||{};
            document.getElementById('d-total').textContent      = s.total         ||0;
            document.getElementById('d-presentes').textContent  = s.presentes     ||0;
            document.getElementById('d-tardios').textContent    = s.tardios       ||0;
            document.getElementById('d-incidencias').textContent= s.con_incidencia||0;
            document.getElementById('d-info').textContent = tablaDataD.length+' registros';
        })
        .catch(()=>setError('d-tbody',10,'Error de conexión'));
}


function setLoading(id,cols) {
    document.getElementById(id).innerHTML=
        `<tr><td colspan="${cols}" class="td-placeholder"><div style="display:flex;align-items:center;justify-content:center;gap:.5rem;"><div class="ra-spinner"></div>Cargando…</div></td></tr>`;
}
function setError(id,cols,msg) {
    document.getElementById(id).innerHTML=`<tr><td colspan="${cols}" style="text-align:center;padding:2rem;color:var(--rojo);">${msg}</td></tr>`;
}

// ══════════════════════════════════
// MODAL EDITAR REGISTRO
// ══════════════════════════════════
function abrirEditar(tabla, id, nombre, estado, hora, incidencia='') {
    document.getElementById('edit-id').value    = id;
    document.getElementById('edit-tabla').value = tabla;
    document.getElementById('modal-edit-titulo').textContent =
        tabla==='diaria' ? '✏️ Editar registro diario' : '✏️ Editar registro de materia';

    document.getElementById('edit-campos-diaria').style.display  = tabla==='diaria'  ? '' : 'none';
    document.getElementById('edit-campos-materia').style.display = tabla==='materia' ? '' : 'none';

    if (tabla==='diaria') {
        document.getElementById('edit-estado-diaria').value = estado;
        document.getElementById('edit-hora').value          = hora || '';
        document.getElementById('edit-incidencia').value    = incidencia || '';
        document.getElementById('edit-obs').value           = '';
    } else {
        document.getElementById('edit-estado-materia').value = estado;
    }

    document.getElementById('edit-info-alumno').textContent = '👤 ' + nombre + '  |  ID: ' + id;
    document.getElementById('modal-editar-bg').classList.add('activo');
}
function cerrarEditar() {
    document.getElementById('modal-editar-bg').classList.remove('activo');
}

function guardarEdicion() {
    const id    = document.getElementById('edit-id').value;
    const tabla = document.getElementById('edit-tabla').value;

    let payload = {id: parseInt(id), tabla, accion:'editar'};

    if (tabla==='diaria') {
        payload.estado        = document.getElementById('edit-estado-diaria').value;
        payload.hora_entrada  = document.getElementById('edit-hora').value;
        payload.incidencia    = document.getElementById('edit-incidencia').value.trim();
        payload.observaciones = document.getElementById('edit-obs').value;
    } else {
        payload.estado = document.getElementById('edit-estado-materia').value;
    }

    fetch('ajax/editarRegistro.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(data=>{
        if (!data.ok) { toast('error','Error al guardar',data.msg); return; }
        cerrarEditar();
        toast('ok','Registro actualizado','Los cambios se guardaron correctamente');
        cargarDiaria();
    })
    .catch(()=>toast('error','Error de conexión',''));
}

function confirmarEliminar() {
    const id     = document.getElementById('edit-id').value;
    const tabla  = document.getElementById('edit-tabla').value;
    const nombre = document.getElementById('edit-info-alumno').textContent;

    if (!confirm(`¿Eliminar este registro?\n${nombre}\n\nEsta acción no se puede deshacer.`)) return;

    fetch('ajax/editarRegistro.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id: parseInt(id), tabla, accion:'eliminar'})
    })
    .then(r=>r.json())
    .then(data=>{
        if (!data.ok) { toast('error','Error al eliminar',data.msg); return; }
        cerrarEditar();
        toast('info','Registro eliminado','');
        cargarDiaria();
    })
    .catch(()=>toast('error','Error de conexión',''));
}

// ── Exportar CSV (incluye incidencia) ──
function exportarCSV() {
    if (!tablaDataD.length) { toast('warn','Sin datos','Filtra primero los registros'); return; }
    const headers = ['Matrícula','Nombre','Apellidos','Grupo','Fecha','Hora','Estado','Incidencia','Turno'];
    const rows = tablaDataD.map(r=>[
        r.matricula, r.nombre, r.apellidos, r.grupo||'', r.fecha,
        r.hora_entrada?.substr(0,5)||'', r.estado,
        r.incidencia||'',
        (r.dispositivo||'').split('|').pop().trim()
    ]);
    const _h=new Date();
    const _f=_h.getFullYear()+'-'+String(_h.getMonth()+1).padStart(2,'0')+'-'+String(_h.getDate()).padStart(2,'0');
    const csv=[headers,...rows].map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,\uFEFF'+encodeURIComponent(csv);
    a.download='asistencia_diaria_'+_f+'.csv';
    a.click();
    toast('ok','CSV exportado',tablaDataD.length+' registros');
}

// ══════════════════════════════════════════════════════════════
// WHATSAPP — funciones
// ══════════════════════════════════════════════════════════════
const WA_GUIAS = {
    callmebot: 'https://www.callmebot.com/blog/free-api-whatsapp-messages/',
    twilio:    'https://www.twilio.com/docs/whatsapp/quickstart',
    ultramsg:  'https://docs.ultramsg.com/',
    '360dialog':'https://docs.360dialog.com/whatsapp-api/whatsapp-api/integration',
    local_wa:  'wa-status.php',
};

function iniciarWhatsApp() {
    actualizarFormWA();
    aplicarPlantilla();
    document.getElementById('wa-mensaje').addEventListener('input', ()=>{
        document.getElementById('wa-char-count').textContent =
            document.getElementById('wa-mensaje').value.length;
    });
}

function actualizarFormWA() {
    const prv = document.getElementById('wa-proveedor').value;
    ['callmebot','twilio','ultramsg','360dialog','local_wa'].forEach(p=>{
        const el = document.getElementById('wa-fields-'+p);
        if (el) el.style.display = p===prv ? '' : 'none';
    });
    document.getElementById('wa-link-guia').href = WA_GUIAS[prv] || '#';
    document.getElementById('wa-link-guia').style.display = prv==='local_wa' ? 'none' : '';

    // Badge activo/inactivo
    const activo = document.getElementById('wa-activo').checked;
    const badge  = document.getElementById('wa-estado-badge');
    badge.textContent = activo ? '● Activo' : '○ Inactivo';
    badge.style.background = activo ? '#2e7d32' : '#c62828';

    // Si es local_wa, verificar estado del servidor
    if (prv === 'local_wa') verificarEstadoLocalWA();
}

function verificarEstadoLocalWA() {
    const el = document.getElementById('wa-local-status');
    if (!el) return;
    el.textContent = '⏳ Verificando servidor…';
    fetch('ajax/wa-proxy.php?action=status')
        .then(r=>r.json())
        .then(data=>{
            if (data.estado === 'servidor_off' || !data.ok && data.estado !== 'esperando_qr') {
                el.innerHTML = '🔴 Servidor no activo — <a href="wa-status.php" target="_blank">ver instrucciones</a>';
            } else if (data.ok && data.estado === 'conectado') {
                el.innerHTML = `🟢 Conectado como <strong>${data.info?.nombre||'—'}</strong> (+${data.info?.telefono||'—'})`;
            } else {
                el.innerHTML = '🟡 Esperando escaneo de QR — <a href="wa-status.php" target="_blank">escanear QR</a>';
            }
        })
        .catch(()=>{ el.innerHTML = '🔴 Servidor no activo — <a href="wa-status.php" target="_blank">ver instrucciones</a>'; });
}

function guardarConfigWA() {
    const payload = {
        activo:    document.getElementById('wa-activo').checked,
        proveedor: document.getElementById('wa-proveedor').value,
        callmebot_apikey:  document.getElementById('wa-callmebot-apikey').value.trim(),
        twilio_sid:        document.getElementById('wa-twilio-sid').value.trim(),
        twilio_token:      document.getElementById('wa-twilio-token').value.trim(),
        twilio_from:       document.getElementById('wa-twilio-from').value.trim(),
        ultramsg_instance: document.getElementById('wa-ultramsg-instance').value.trim(),
        ultramsg_token:    document.getElementById('wa-ultramsg-token').value.trim(),
        dialog360_apikey:  document.getElementById('wa-dialog360-apikey').value.trim(),
        plantilla_falta:      document.getElementById('wa-tpl-falta').value.trim(),
        plantilla_tardanza:   document.getElementById('wa-tpl-tardanza').value.trim(),
        plantilla_incidencia: document.getElementById('wa-tpl-incidencia').value.trim(),
    };
    fetch('ajax/guardarConfigWA.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(data=>{
        toast(data.ok?'ok':'error', data.msg, '');
    })
    .catch(()=>toast('error','Error de conexión',''));
}

function cambiarEnvio(tipo, btn) {
    ['individual','grupal','faltantes'].forEach(t=>{
        document.getElementById('wa-envio-'+t).style.display = t===tipo ? '' : 'none';
    });
    document.querySelectorAll('#panel-whatsapp .ra-lista-tab').forEach(b=>b.classList.remove('activa'));
    btn.classList.add('activa');
    aplicarPlantilla();
}

function aplicarPlantilla() {
    const cat = document.getElementById('wa-categoria').value;
    if (cat === 'personalizado') return;

    const tplMap = {
        falta:      document.getElementById('wa-tpl-falta').value,
        tardanza:   document.getElementById('wa-tpl-tardanza').value,
        incidencia: document.getElementById('wa-tpl-incidencia').value,
    };
    const hoy = new Date();
    const fecha = hoy.toLocaleDateString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric'});
    const hora  = hoy.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'});

    let tpl = tplMap[cat] || '';
    // Sustituir variables de alumno si hay uno seleccionado
    if (waAlumnoActual) {
        tpl = tpl.replace(/{nombre}/g,    waAlumnoActual.nombre   || '')
                 .replace(/{apellidos}/g, waAlumnoActual.apellidos|| '')
                 .replace(/{grupo}/g,     waAlumnoActual.grupo    || '')
                 .replace(/{incidencia}/g,waAlumnoActual.incidencia||INCIDENCIA_ACTIVA||'sin especificar');
    }
    tpl = tpl.replace(/{fecha}/g, fecha).replace(/{hora}/g, hora);

    document.getElementById('wa-mensaje').value = tpl;
    document.getElementById('wa-char-count').textContent = tpl.length;
}

function buscarAlumnoWA() {
    const q = document.getElementById('wa-buscar-alumno').value.trim();
    if (!q) return;
    const el = document.getElementById('wa-alumno-resultado');
    el.innerHTML = '<span style="color:var(--muted);">Buscando…</span>';

    fetch('ajax/buscarAlumno.php?matricula='+encodeURIComponent(q))
        .then(r=>r.json())
        .then(data=>{
            if (!data.ok || !data.alumno) {
                el.innerHTML = '<span style="color:var(--rojo);">Alumno no encontrado</span>';
                waAlumnoActual = null; return;
            }
            waAlumnoActual = data.alumno;
            const tel = data.alumno.telefono || '(sin teléfono)';
            el.innerHTML = `<strong>👤 ${esc(data.alumno.apellidos+' '+data.alumno.nombre)}</strong>
                — 📱 ${esc(tel)}
                <span class="ra-badge rb-grupo">${esc(data.alumno.nombre_grupo||'')}</span>`;
            aplicarPlantilla();
        })
        .catch(()=>{ el.innerHTML='<span style="color:var(--rojo);">Error de conexión</span>'; });
}

function cargarFaltantesWA() {
    const el = document.getElementById('wa-faltantes-lista');
    el.innerHTML = '<span style="color:var(--muted);">Cargando…</span>';
    fetch('ajax/obtenerFaltantes.php')
        .then(r=>r.json())
        .then(data=>{
            waFaltantesHoy = data.faltantes || [];
            const conTel = waFaltantesHoy.filter(a=>a.telefono);
            el.innerHTML = waFaltantesHoy.length
                ? `<strong>${waFaltantesHoy.length}</strong> sin registrar hoy — 
                   <strong>${conTel.length}</strong> con teléfono registrado`
                : '🎉 ¡Todos registrados hoy!';
        })
        .catch(()=>{ el.innerHTML='<span style="color:var(--rojo);">Error</span>'; });
}

function enviarNotificacion() {
    const tipoEnvio = document.querySelector('#panel-whatsapp .ra-lista-tab.activa')?.id.replace('btn-envio-','') || 'individual';
    const categoria = document.getElementById('wa-categoria').value;
    const mensaje   = document.getElementById('wa-mensaje').value.trim();

    if (!mensaje) { toast('warn','Mensaje vacío','Escribe o genera un mensaje antes de enviar'); return; }

    let payload = { tipo: tipoEnvio, categoria, mensaje };

    if (tipoEnvio === 'individual') {
        if (!waAlumnoActual) { toast('warn','Sin alumno','Busca un alumno primero'); return; }
        payload.id_alumno = waAlumnoActual.id_alumno;
    } else if (tipoEnvio === 'grupal') {
        const grp = document.getElementById('wa-grupo-sel').value;
        if (!grp) { toast('warn','Sin grupo','Selecciona un grupo'); return; }
        payload.id_grupo = parseInt(grp);
    } else if (tipoEnvio === 'faltantes') {
        if (!waFaltantesHoy.length) { toast('warn','Sin faltantes','Carga los faltantes primero'); return; }
        payload.destinatarios = waFaltantesHoy.filter(a=>a.telefono).map(a=>a.telefono);
        if (!payload.destinatarios.length) { toast('warn','Sin teléfonos','Ningún faltante tiene teléfono registrado'); return; }
    }

    const btn = document.getElementById('btn-enviar-wa');
    btn.disabled = true; btn.textContent = '⏳ Enviando…';
    document.getElementById('wa-resultado').textContent = '';

    fetch('ajax/enviarNotificacion.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(data=>{
        btn.disabled = false; btn.textContent = '📱 Enviar por WhatsApp';
        const el = document.getElementById('wa-resultado');
        el.style.color = data.ok ? '#2e7d32' : '#c62828';
        el.textContent = data.msg;
        toast(data.ok?'ok':'error', data.msg, data.errores?.join(', ')||'');
    })
    .catch(()=>{
        btn.disabled=false; btn.textContent='📱 Enviar por WhatsApp';
        toast('error','Error de conexión','');
    });
}
</script>
</body>
</html>
