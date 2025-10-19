<?php
class Admin {
    // Leer administrador por correo + contraseña
    public function leerAdmin($correo, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE correo = ?");
        $stmt->execute([$correo]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }
}

class Profesor {
    // Leer profesor por correo + contraseña
    public function leerProfesor($correo, $password) {
        global $pdo;

        // Quitamos espacios por seguridad
        $correo = trim($correo);
        $password = trim($password);

        // Buscamos al profesor por correo
        $stmt = $pdo->prepare("SELECT * FROM profesor WHERE correo = ?");
        $stmt->execute([$correo]);
        $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos la contraseña usando password_verify
        if ($profesor && isset($profesor['password']) && password_verify($password, $profesor['password'])) {
            return $profesor; // Devuelve todos los datos del profesor
        }

        // Si no coincide o no existe
        return false;
    }
}

class Alumno {
    // Buscar alumno por matrícula y CURP
    public function buscarPorMatriculaYCurp($matricula, $curp) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM alumno WHERE matricula = ? AND curp = ?");
        $stmt->execute([$matricula, $curp]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
