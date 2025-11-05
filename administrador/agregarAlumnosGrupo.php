<?php
session_start();
require_once __DIR__ . "/../assets/sentenciasSQL/Conexion.php";

// Permisos: sólo admin
if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
	header('Location: ../index.php');
	exit;
}

// Verificar idGrupo
if (!isset($_GET['idGrupo'])) {
	header('Location: gruposCreados.php');
	exit;
}

$idGrupo = intval($_GET['idGrupo']);

// Manejar acciones POST: agregar o eliminar (retirar) alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Agregar alumnos al grupo (puede ser array)
	if (isset($_POST['add']) && isset($_POST['alumnos'])) {
		$alumnos = (array) $_POST['alumnos'];
		// filtrar ids válidos
		$ids = array_map('intval', $alumnos);
		if (!empty($ids)) {
			// construir placeholders
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$sql = "UPDATE alumno SET id_grupo = ? WHERE id_alumno IN ($placeholders)";
			$stmt = $pdo->prepare($sql);
			$params = array_merge([$idGrupo], $ids);
			try {
				$stmt->execute($params);
				echo "<script>alert('Alumnos agregados/actualizados correctamente.'); window.location='agregarAlumnosGrupo.php?idGrupo={$idGrupo}';</script>";
				exit;
			} catch (Exception $e) {
				$error = 'Error al asignar alumnos: ' . $e->getMessage();
			}
		}
	}

	// Retirar alumno del grupo (poner id_grupo = NULL)
	if (isset($_POST['remove']) && isset($_POST['id_alumno'])) {
		$idAlumno = intval($_POST['id_alumno']);
		$sql = "UPDATE alumno SET id_grupo = NULL WHERE id_alumno = :id";
		$stmt = $pdo->prepare($sql);
		try {
			$stmt->execute([':id' => $idAlumno]);
			echo "<script>alert('Alumno retirado del grupo.'); window.location='agregarAlumnosGrupo.php?idGrupo={$idGrupo}';</script>";
			exit;
		} catch (Exception $e) {
			$error = 'Error al retirar alumno: ' . $e->getMessage();
		}
	}
}

// Obtener datos del grupo (si existe)
$stmtG = $pdo->prepare('SELECT * FROM grupo WHERE idGrupo = :id');
$stmtG->execute([':id' => $idGrupo]);
$grupo = $stmtG->fetch(PDO::FETCH_ASSOC);
if (!$grupo) {
	die('Grupo no encontrado.');
}

// Alumnos pertenecientes al grupo
$stmt = $pdo->prepare('SELECT id_alumno, matricula, nombre, apellidos, numero_lista FROM alumno WHERE id_grupo = :idGrupo ORDER BY numero_lista ASC');
$stmt->execute([':idGrupo' => $idGrupo]);
$alumnosGrupo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alumnos disponibles en el sistema (no en este grupo) - pueden pertenecer a otro grupo o ser null
$stmt2 = $pdo->prepare('SELECT id_alumno, matricula, nombre, apellidos, numero_lista, id_grupo FROM alumno WHERE id_grupo IS NULL OR id_grupo != :idGrupo ORDER BY apellidos, nombre');
$stmt2->execute([':idGrupo' => $idGrupo]);
$alumnosDisponibles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Alumnos del grupo: <?= htmlspecialchars($grupo['nombre']) ?></title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<style>
html, body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f0e8dc;
  display: flex;
  justify-content: center;
  padding: 10px;
}

.box {
  position: relative;
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 1100px;
  overflow-x: auto;
}

h1 {
  font-size: 1.6rem;
  color: #8b4513;
  text-shadow: 1px 1px #f5deb3;
  margin: 0;
}

p, h3, h4 {
  color: #4b3621;
}

p {
  margin-bottom: 1rem;
}

h3 {
  font-size: 1.2rem;
  color: #5c4033;
  margin-top: 1.8rem;
}

h4 {
  color: #5c4033;
  margin: 10px 0;
}

.back {
  position: absolute;
  top: 12px;
  right: 15px;
  background-color: #a0522d;
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  padding: 8px 14px;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}
.back:hover {
  background-color: #deb887;
  color: #4b2e05;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 900px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-top: 10px;
}
th, td {
  border: 1px solid #cfcfcf;
  padding: 8px;
  text-align: center;
}
th {
  background-color: #f5deb3;
  color: #5c4033;
}
td {
  color: #4b3621;
}

select,
input[type="number"],
input[type="text"] {
  padding: 0.5rem 0.8rem;
  border: 1px solid #c2a88c;
  border-radius: 6px;
  background-color: rgba(255, 250, 240, 0.95);
  font-size: 1rem;
  color: #5c4033;
  transition: border-color 0.2s ease;
}
select:hover,
input[type="text"]:hover {
  border-color: #a0522d;
}

.btn {
  padding: 0.6rem 1rem;
  background-color: #deb887;
  color: #3b2f2f;
  border: 1px solid #a0522d;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease, transform 0.1s ease;
}
.btn:hover {
  background-color: #d2b48c;
  transform: translateY(-1px);
}
.btn-danger {
  background-color: #ff6b6b;
  color: #fff;
  border: 1px solid #a52a2a;
}
.btn-danger:hover {
  background-color: #ff4c4c;
}

form.inline {
  display: inline;
}

#contenedorAlumnos {
  margin-top: 10px;
}

#contenedorAlumnos label {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: #fffaf0;
  border: 1px solid #c2a88c;
  border-radius: 6px;
  padding: 6px 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}
#contenedorAlumnos label:hover {
  background-color: #f5deb3;
}

#contenedorAlumnos input[type="checkbox"] {
  accent-color: #a0522d;
  transform: scale(1.1);
}

#contenedorAlumnos h4 {
  color: #5c4033;
  text-shadow: 1px 1px #f5deb3;
  margin-bottom: 8px;
}

#grupoSelect {
  margin-top: 6px;
  margin-bottom: 8px;
  background-color: #fffaf0;
}

strong {
  color: #5c4033;
}
</style>

</head>
<body>
	<div class="box">
		<a class="btn back" href="gruposCreados.php">&#8592; Volver</a>
		<h1>Alumnos del grupo: <?= htmlspecialchars($grupo['nombre']) ?></h1>
		<p><strong>Descripción:</strong> <?= htmlspecialchars($grupo['descripcion']) ?></p>

		<?php if (!empty($error)): ?>
			<div style="color:red; margin-bottom:12px"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<h3>Alumnos asignados (<?= count($alumnosGrupo) ?>)</h3>
		<?php if (empty($alumnosGrupo)): ?>
			<p>No hay alumnos asignados a este grupo.</p>
		<?php else: ?>
			<table>
				<thead>
					<tr><th>No.</th><th>Matrícula</th><th>Alumno</th><th>Acciones</th></tr>
				</thead>
				<tbody>
					<?php foreach ($alumnosGrupo as $al): ?>
						<tr>
							<td><?= htmlspecialchars($al['numero_lista']) ?></td>
							<td><?= htmlspecialchars($al['matricula']) ?></td>
							<td><?= htmlspecialchars($al['apellidos'].' '.$al['nombre']) ?></td>
							<td>
								<form class="inline" method="post" onsubmit="return confirm('¿Retirar a este alumno del grupo?');">
									<input type="hidden" name="id_alumno" value="<?= $al['id_alumno'] ?>">
									<button type="submit" name="remove" class="btn btn-danger">Retirar</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>


<h3 style="margin-top:18px">Agregar alumnos existentes</h3>
<p>Selecciona uno o varios alumnos para asignarlos a este grupo. Los alumnos que ya pertenezcan a otro grupo se actualizarán para pertenecer a este.</p>

<?php if (empty($alumnosDisponibles)): ?>
	<p>No hay alumnos disponibles en el sistema.</p>
<?php else: ?>
	<?php
	// Obtener todos los grupos (excepto el actual)
	$stmtGAll = $pdo->prepare("SELECT idGrupo, nombre FROM grupo WHERE idGrupo != :idGrupo ORDER BY nombre");
	$stmtGAll->execute([':idGrupo' => $idGrupo]);
	$gruposExistentes = $stmtGAll->fetchAll(PDO::FETCH_ASSOC);

	// Agrupar alumnos por ID de grupo (más 'null' para los sin grupo)
	$gruposAlumnos = [];

	// Sin grupo (id_grupo = NULL)
	$gruposAlumnos['0'] = [
		'nombre' => 'Sin grupo',
		'alumnos' => array_values(array_filter($alumnosDisponibles, fn($a) => $a['id_grupo'] === null))
	];

	// Con grupo
	foreach ($gruposExistentes as $g) {
		$gruposAlumnos[$g['idGrupo']] = [
			'nombre' => $g['nombre'],
			'alumnos' => array_values(array_filter($alumnosDisponibles, fn($a) => $a['id_grupo'] == $g['idGrupo']))
		];
	}
	?>

	<form method="post">
		<label for="grupoSelect"><strong>Selecciona un grupo para ver sus alumnos:</strong></label>
		<select id="grupoSelect" required class="btn" style="margin-left:8px;">
			<option value="">-- Elegir grupo --</option>
			<option value="0">Sin grupo</option>
			<?php foreach ($gruposExistentes as $g): ?>
				<option value="<?= $g['idGrupo'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
			<?php endforeach; ?>
		</select>

		<div id="contenedorAlumnos" style="margin-top:16px;">
			<p style="color:#555;">Selecciona un grupo para mostrar sus alumnos.</p>
		</div>

		<div style="margin-top:12px;">
			<button type="submit" name="add" class="btn">Agregar seleccionados</button>
		</div>
	</form>

	<script>
		const data = <?= json_encode($gruposAlumnos, JSON_UNESCAPED_UNICODE) ?>;
		const contenedor = document.getElementById('contenedorAlumnos');
		const select = document.getElementById('grupoSelect');

		select.addEventListener('change', () => {
			const grupoID = select.value;
			contenedor.innerHTML = '';

			if (!grupoID || !data[grupoID]) {
				contenedor.innerHTML = '<p style="color:#555;">Selecciona un grupo para mostrar sus alumnos.</p>';
				return;
			}

			const grupo = data[grupoID];
			const alumnos = grupo.alumnos;

			if (alumnos.length === 0) {
				contenedor.innerHTML = `<p style="color:#777;">No hay alumnos en ${grupo.nombre}.</p>`;
				return;
			}

			const titulo = document.createElement('h4');
			titulo.textContent = `Alumnos en ${grupo.nombre}:`;
			contenedor.appendChild(titulo);

			const grid = document.createElement('div');
			grid.style.display = 'grid';
			grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(220px, 1fr))';
			grid.style.gap = '6px';

			alumnos.forEach(al => {
				const label = document.createElement('label');
				label.style.display = 'flex';
				label.style.alignItems = 'center';
				label.style.gap = '6px';
				label.style.background = '#fff';
				label.style.border = '1px solid #ccc';
				label.style.padding = '6px';
				label.style.borderRadius = '6px';
				label.style.cursor = 'pointer';

				const input = document.createElement('input');
				input.type = 'checkbox';
				input.name = 'alumnos[]';
				input.value = al.id_alumno;

				const span = document.createElement('span');
				span.textContent = `${al.apellidos} ${al.nombre} (${al.matricula})`;

				label.appendChild(input);
				label.appendChild(span);
				grid.appendChild(label);
			});

			contenedor.appendChild(grid);
		});
	</script>
<?php endif; ?>
