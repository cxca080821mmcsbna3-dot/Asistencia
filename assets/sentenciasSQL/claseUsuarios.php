<?php
class Admin {
    public function leerAdmin($usuario, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos la contraseña hasheada con password_verify
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }
}

class Profesor {
    public function buscarPorNombreYCorreo($nombreCompleto, $correo) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT * FROM profesor 
            WHERE LOWER(CONCAT(nombre, ' ', apellidos)) = LOWER(?) 
            AND correo = ?
        ");
        $stmt->execute([$nombreCompleto, $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class Alumno {
    public function buscarPorNombreYCorreo($nombreCompleto, $correo) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT * FROM alumno 
            WHERE LOWER(CONCAT(nombre, ' ', apellidos)) = LOWER(?) 
            AND correo = ?
        ");
        $stmt->execute([$nombreCompleto, $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
