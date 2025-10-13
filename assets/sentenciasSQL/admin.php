<?php
class Admin {
    public function leerAdmin($usuario, $contrasena) {
        include "Conexion.php";
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = :usuario AND password = :contrasena LIMIT 1");
        $stmt->execute([
            ':usuario' => $usuario,
            ':contrasena' => $contrasena
        ]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        return $admin ? $admin : false;
    }
    public function actualizarAdmin($id, $usuario, $contrasena) {
        include "Conexion.php";
        $contrasena_hash=password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt =$pdo->prepare("UPDATE admin SET usuario = :usuario, password = :password WHERE idAdmin = :id");
        return $stmt->execute([
            ':usuario' => $usuario,
            ':password' => $contrasena_hash,
            ':id' => $id
        ]);
    }
    public function obtenerAdminPorId($id) {
        include "Conexion.php"; 
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE idAdmin = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>