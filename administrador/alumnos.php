<?php
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

// --- Cargar grupos disponibles ---
$stmt = $pdo->query("SELECT idGrupo, nombre FROM grupo ORDER BY nombre ASC");
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Procesar formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idGrupo = intval($_POST['idGrupo']);
    $alumnos = $_POST['alumnos'] ?? [];

    $sql = "INSERT INTO alumno (numero_lista, matricula, nombre, apellidos, telefono, id_grupo, curp)
            VALUES (:numero_lista, :matricula, :nombre, :apellidos, :telefono, :id_grupo, :curp)";
    $stmt = $pdo->prepare($sql);

    foreach ($alumnos as $a) {
        if (trim($a['nombre']) === '' || trim($a['apellidos']) === '') continue;
        $stmt->execute([
            ':numero_lista' => $a['numero_lista'] ?? null,
            ':matricula' => $a['matricula'] ?? null,
            ':nombre' => $a['nombre'],
            ':apellidos' => $a['apellidos'],
            ':telefono' => $a['telefono'] ?? null,
            ':id_grupo' => $idGrupo,
            ':curp' => $a['curp'] ?? null
        ]);
    }

    echo "<script>alert('✅ Alumnos registrados correctamente');window.location='registrarAlumnos.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar alumnos</title>
<style>
body{font-family:Segoe UI;background:#f2eee6;padding:20px;display:flex;justify-content:center;}
.container{background:white;padding:20px;border-radius:12px;box-shadow:0 0 10px rgba(0,0,0,0.2);max-width:1100px;width:100%;}
h1{color:#4b3621;text-align:center;margin-bottom:20px;}
select, input{padding:6px;border-radius:6px;border:1px solid #ccc;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:6px;text-align:center;}
th{background:#d2b48c;color:#3b2a1a;}
button{background:#8b4513;color:white;padding:8px 12px;border:none;border-radius:6px;cursor:pointer;margin:5px;}
button:hover{background:#a0522d;}
.add-btn{background:#3c7a3c;}
.add-btn:hover{background:#2e5c2e;}
</style>
</head>
<body>
<div class="container">
  <h1>Registrar Alumnos por Grupo</h1>

  <form method="POST">
    <label for="idGrupo">Seleccionar grupo:</label>
    <select name="idGrupo" id="idGrupo" required>
      <option value="">-- Elige un grupo --</option>
      <?php foreach($grupos as $g): ?>
        <option value="<?= $g['idGrupo'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
      <?php endforeach; ?>
    </select>

    <table id="tablaAlumnos">
      <thead>
        <tr>
          <th>No. Lista</th>
          <th>Matrícula</th>
          <th>Nombre</th>
          <th>Apellidos</th>
          <th>Teléfono</th>
          <th>CURP</th>
          <th>Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><input type="number" name="alumnos[0][numero_lista]" style="width:60px;"></td>
          <td><input type="text" name="alumnos[0][matricula]" style="width:120px;"></td>
          <td><input type="text" name="alumnos[0][nombre]" required></td>
          <td><input type="text" name="alumnos[0][apellidos]" required></td>
          <td><input type="text" name="alumnos[0][telefono]" style="width:100px;"></td>
          <td><input type="text" name="alumnos[0][curp]" style="width:160px;"></td>
          <td><button type="button" onclick="eliminarFila(this)">❌</button></td>
        </tr>
      </tbody>
    </table>

    <button type="button" class="add-btn" onclick="agregarFila()">Agregar alumno</button>
    <button type="submit">Guardar todos</button>
  </form>
</div>

<script>
let contador = 1;

function agregarFila() {
  const tbody = document.querySelector("#tablaAlumnos tbody");
  const fila = document.createElement("tr");
  fila.innerHTML = `
    <td><input type="number" name="alumnos[${contador}][numero_lista]" style="width:60px;"></td>
    <td><input type="text" name="alumnos[${contador}][matricula]" style="width:120px;"></td>
    <td><input type="text" name="alumnos[${contador}][nombre]" required></td>
    <td><input type="text" name="alumnos[${contador}][apellidos]" required></td>
    <td><input type="text" name="alumnos[${contador}][telefono]" style="width:100px;"></td>
    <td><input type="text" name="alumnos[${contador}][curp]" style="width:160px;"></td>
    <td><button type="button" onclick="eliminarFila(this)">❌</button></td>`;
  tbody.appendChild(fila);
  contador++;
}

function eliminarFila(btn) {
  btn.closest("tr").remove();
}
</script>
</body>
</html>
