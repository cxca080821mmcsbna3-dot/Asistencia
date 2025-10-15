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
    // Buscar profesor por correo
    public function buscarPorCorreo($correo) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM profesor WHERE correo = ?");
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
