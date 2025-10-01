<?php
session_start();
if (!isset($_SESSION['idAdmin']) && !isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once '../assets/sentenciasSQL/Docentes.php';
$docente = new Docentes();

if (!isset($_GET['id'])) {
    echo "<script>alert('ID de docente no especificado'); window.location.href = 'docentes.php';</script>";
    exit;
}

$id = $_GET['id'];

$eliminado = $docente->eliminar($id);

if ($eliminado) {
    echo "<script>alert('Docente eliminado correctamente'); window.location.href = 'docentes.php';</script>";
} else {
    echo "<script>alert('Error al eliminar docente'); window.location.href = 'docentes.php';</script>";
}
?>
