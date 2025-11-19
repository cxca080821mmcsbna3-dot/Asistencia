<?php
// DATOS DE LA BASE DE DATOS
$dsn = 'mysql:host=localhost;dbname=asistencia;charset=utf8mb4';
$username = 'root';
$password = '';

try {
    // CREAR LA CONEXIÓN
    $pdo = new PDO($dsn, $username, $password);
    // ESTABLECER MODO DE ERROR
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // FUERZA LA CONEXIÓN A UTF-8MB4 (por si acaso)
    $pdo->exec("SET NAMES utf8mb4");

    // 🔹 INICIALIZAR SESIÓN (si no está iniciada)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 🔹 SI HAY UN USUARIO LOGEADO, MANDARLO A MYSQL
    if (!empty($_SESSION['nombre'])) {
        $usuario = $_SESSION['nombre'];
        $stmt = $pdo->prepare("SET @usuario_logeado = :usuario");
        $stmt->execute(['usuario' => $usuario]);
    } else {
        // En caso de que no haya usuario (por seguridad, se pone NULL)
        $pdo->exec("SET @usuario_logeado = NULL");
    }

} catch (PDOException $e) {
    die("CONEXIÓN FALLIDA: " . $e->getMessage());
}
?>
