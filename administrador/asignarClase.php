<?php
require_once __DIR__ . "/../assets/sentenciasSQL/conexion.php";

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Traer datos de las tablas
$grupos = $pdo->query("SELECT idGrupo, nombre FROM grupo")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $pdo->query("SELECT id_profesor, nombre FROM profesor")->fetchAll(PDO::FETCH_ASSOC);

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idGrupo = $_POST['idGrupo'];
    $idMateria = $_POST['idMateria'];
    $idProfesor = $_POST['idProfesor'];

    $sql = "INSERT INTO grupo_materia (id_grupo, id_materia, id_profesor) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$idGrupo, $idMateria, $idProfesor])) {
        echo "<script>alert('Clase asignada correctamente'); window.location='asignarClase.php';</script>";
    } else {
        echo "<script>alert('Error al asignar la clase');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Clase</title>
    <link rel="stylesheet" href="css/asignarClase.css">
</head>
<style>
    /* 🎓 Estilo general */
body {
    margin: 0;
    padding: 0;
    background-color: #f4ecdf;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #4b3621;
}

/* 🔙 Flecha de regreso */
.back-arrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    margin: 20px;
    color: #a0522d;
    text-decoration: none;
    font-weight: bold;
    background-color: #fff5e1;
    border: 1px solid #deb887;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.back-arrow:hover {
    background-color: #deb887;
    color: #fff;
    transform: translateX(-4px);
}

/* 🏷️ Títulos */
h1, h2 {
    text-align: center;
    color: #8b4513;
    margin-bottom: 20px;
}

/* 📦 Contenedor general */
.container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 40px;
    padding: 40px;
    flex-wrap: wrap;
}

/* 🧾 Secciones (tarjetas) */
.card {
    background-color: #fffaf0;
    border: 1px solid #deb887;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    padding: 25px 30px;
    width: 460px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

/* 📋 Formularios */
form {
    width: 100%;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #5c4033;
}

select, input[type="text"], input[type="number"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d2b48c;
    border-radius: 6px;
    font-size: 1em;
    background-color: #fff5e6;
    margin-bottom: 15px;
    color: #4b3621;
    box-sizing: border-box;
}

select:focus, input[type="text"]:focus, input[type="number"]:focus {
    outline: none;
    border-color: #a0522d;
    background-color: #fffaf0;
}

/* 🔘 Botones */
input[type="submit"], .btn {
    align-self: flex-end;
    padding: 10px 18px;
    color: #fff;
    background-color: #a0522d;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

input[type="submit"]:hover, .btn:hover {
    background-color: #8b4513;
    transform: translateY(-2px);
}

/* 🌙 Modo oscuro (opcional si usas toggle en otras páginas) */
body.dark-mode {
    background-color: #2e2e2e;
    color: #f0e8dc;
}

body.dark-mode .card {
    background-color: #4a4a4a;
    border-color: #a67b5b;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
}

body.dark-mode h1, body.dark-mode h2 {
    color: #ffd39b;
}

body.dark-mode label {
    color: #ffdead;
}

body.dark-mode select,
body.dark-mode input[type="text"],
body.dark-mode input[type="number"] {
    background-color: #555;
    color: #f0e8dc;
    border: 1px solid #deb887;
}

body.dark-mode input[type="submit"],
body.dark-mode .btn {
    background-color: #6b4c2a;
}

body.dark-mode input[type="submit"]:hover,
body.dark-mode .btn:hover {
    background-color: #8b5a2b;
}

body.dark-mode .back-arrow {
    background-color: #3c3c3c;
    color: #deb887;
    border-color: #a67b5b;
}

    </style>
<body>
<?php include_once "layout/header_admin.php"; ?>

<h1>Asignar Materia y Grupo a un Profesor</h1>

<div class="container">
    <div class="card">
        <form method="POST">

            <label for="idGrupo">Grupo:</label>
            <select name="idGrupo" id="idGrupo" required>
                <option value="">Selecciona un grupo</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= $grupo['idGrupo'] ?>">
                        <?= htmlspecialchars($grupo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="idMateria">Materia:</label>
            <select name="idMateria" id="idMateria" required>
                <option value="">Selecciona una materia</option>
            </select>

            <label for="idProfesor">Profesor:</label>
            <select name="idProfesor" required>
                <option value="">Selecciona un profesor</option>
                <?php foreach ($profesores as $profesor): ?>
                    <option value="<?= $profesor['id_profesor'] ?>">
                        <?= htmlspecialchars($profesor['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <center><input type="submit" value="Asignar Clase"></center>
        </form>
    </div>
</div>

<script>
document.getElementById('idGrupo').addEventListener('change', function () {
    const materiaSelect = document.getElementById('idMateria');

    materiaSelect.innerHTML = '<option value="">Cargando materias...</option>';

    if (!this.value) {
        materiaSelect.innerHTML = '<option value="">Selecciona una materia</option>';
        return;
    }

    // El semestre se obtiene del PRIMER DIGITO del NOMBRE del grupo (101 → 1)
    const textoGrupo = this.options[this.selectedIndex].text;
    const semestre = parseInt(textoGrupo.charAt(0));

    fetch(`obtenerMaterias.php?semestre=${semestre}`)
        .then(response => response.json())
        .then(data => {
            materiaSelect.innerHTML = '<option value="">Selecciona una materia</option>';

            if (data.length === 0) {
                materiaSelect.innerHTML = '<option value="">No hay materias</option>';
                return;
            }

            data.forEach(materia => {
                const option = document.createElement('option');
                option.value = materia.id_materia;
                option.textContent = materia.nombre;
                materiaSelect.appendChild(option);
            });
        })
        .catch(() => {
            materiaSelect.innerHTML = '<option value="">Error al cargar materias</option>';
        });
});
</script>


</body>
</html>
