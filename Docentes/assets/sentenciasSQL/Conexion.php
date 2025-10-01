<?php
$host = 'localhost';
$dbname = 'asistencia'; // Asegúrate que aquí sea la base correcta
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("CONEXIÓN FALLIDA: " . $e->getMessage());
}
?>
