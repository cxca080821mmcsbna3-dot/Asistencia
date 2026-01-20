<?php

include_once(__DIR__ . '/../assets/sentenciasSQL/grupos.php');
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nombreAdmin = $_SESSION['nombre'];

if (isset($_POST['crear'])) {

    $idGrupo       = random_int(10000000, 99999999);
    $nombre_grupo  = htmlspecialchars(trim($_POST['nombre_grupo']), ENT_QUOTES, 'UTF-8');
    $semestre      = htmlspecialchars(trim($_POST['semestre']), ENT_QUOTES, 'UTF-8');
    $tutor         = htmlspecialchars(trim($_POST['tutor']), ENT_QUOTES, 'UTF-8');

    $crear_grupos = new Grupos();
    $crear = $crear_grupos->crearGrupo($idGrupo, $nombre_grupo, $semestre, $tutor);

    if ($crear === true) {
        echo "<script>alert('Grupo creado exitosamente'); window.location='crearGrupo.php';</script>";
        exit();
    } elseif ($crear === 'duplicado') {
        echo "<script>alert('Grupo ya existente. Intenta de nuevo.');</script>";
    } else {
        echo "<script>alert('Error al crear el grupo.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Grupo</title>

<style>

    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0e8dc;
    margin: 0;
    padding: 20px;
    color: #4b3621;
}

/* Flecha regresar */
.back-arrow {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 8px 7px;
    margin: 4px;
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

/* Contenedor del formulario */
.form-container {
    max-width: 520px;
    margin: 40px auto;
    background-color: #fffaf0;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

/* Título */
.form-container h2 {
    text-align: center;
    color: #8b4513;
    margin-bottom: 25px;
}

/* Labels */
.form-container label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #5c4033;
}

/* Inputs y textareas */
.form-container input,
.form-container textarea {
    width: 96%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #c8b6a6;
    font-size: 14px;
    margin-bottom: 18px;
    transition: border 0.3s, box-shadow 0.3s;
    background-color: #fff;
}

.form-container textarea {
    resize: vertical;
    min-height: 80px;
}

.form-container input:focus,
.form-container textarea:focus {
    outline: none;
    border-color: #a0522d;
    box-shadow: 0 0 6px rgba(160, 82, 45, 0.4);
}

/* Botón */
.btn {
    width: 100%;
    padding: 12px;
    background-color: #a0522d;
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}

.btn:hover {
    background-color: #8b4513;
    transform: translateY(-2px);
}

/* Validaciones */
input.valid, textarea.valid {
    border: 2px solid green;
    background: #e8f5e9;
}

input.invalid, textarea.invalid {
    border: 2px solid red;
    background: #ffebee;
}

/* 🌙 Modo oscuro */
body.dark-mode {
    background-color: #2c2c2c;
    color: #f0e8dc;
}

body.dark-mode .form-container {
    background-color: #4a4a4a;
}

body.dark-mode h2 {
    color: #ffd39b;
}

body.dark-mode label {
    color: #f5deb3;
}

body.dark-mode input,
body.dark-mode textarea {
    background-color: #5a5a5a;
    color: #fff;
    border-color: #888;
}

body.dark-mode .btn {
    background-color: #6b4c2a;
}

body.dark-mode .btn:hover {
    background-color: #8b5a2b;
}

input.valid, textarea.valid {
  border: 2px solid green;
  background: #e8f5e9;
}
input.invalid, textarea.invalid {
  border: 2px solid red;
  background: #ffebee;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0e8dc;
    margin: 0;
    padding: 20px;
}

.form-container {
    max-width: 520px;
    margin: 40px auto;
    background-color: #fffaf0;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

label {
    font-weight: bold;
}

input, textarea, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 18px;
    border-radius: 10px;
    border: 1px solid #c8b6a6;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #a0522d;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: bold;
}
</style>
</head>

<body>

<a href="gruposCreados.php">&#8592; Regresar</a>

<div class="form-container">
<h2>Agregar Grupo</h2>

<form method="POST">

<label>Nombre del Grupo</label>
<input type="text" name="nombre_grupo" required>

<label>Semestre</label>
<select name="semestre" required>
<option value="">Selecciona un semestre</option>
<option>Primer semestre</option>
<option>Segundo semestre</option>
<option>Tercer semestre</option>
<option>Cuarto semestre</option>
<option>Quinto semestre</option>
<option>Sexto semestre</option>
</select>

<label>Tutor</label>
<textarea name="tutor"></textarea>

<button type="submit" name="crear">Agregar Grupo</button>

</form>
</div>

</body>
</html>
