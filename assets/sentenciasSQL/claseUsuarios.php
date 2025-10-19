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
    

    // Leer profesor por correo y contraseña
    public function leerProfesor($correo, $password) {
        global $pdo;
        $correo = trim($correo);
        $password = trim($password);

        if (empty($correo) || empty($password)) return false;

        $stmt = $pdo->prepare("SELECT * FROM profesor WHERE correo = ?");
        $stmt->execute([$correo]);
        $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profesor && isset($profesor['password']) && password_verify($password, $profesor['password'])) {
            return $profesor;
        }
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
