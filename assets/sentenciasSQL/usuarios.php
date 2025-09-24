<?php

class Usuarios{

    public function darAlta($idUsuario, $nombre, $apellidos, $lada, $telefono, $correo, $medioE, $origen, $pais){
        include 'Conexion.php';
        $stmt = $pdo->prepare("INSERT INTO registros (idR, nombre, apellidos, lada, telefono, correo, medioE, origen, pais)
             VALUES(:idUsuario, :nombre, :apellidos, :lada, :telefono, :correo, :medioE, :origen, :pais)"
        );

        try {
            $alta = $stmt->execute([
                ':idUsuario' => $idUsuario,
                ':nombre'    => $nombre,
                ':apellidos' => $apellidos,
                ':lada'      => $lada,
                ':telefono'  => $telefono,
                ':correo'    => $correo,
                ':medioE'    => $medioE,
                ':origen'    => $origen,
                ':pais'      => $pais
            ]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                // Clave duplicada (correo/teléfono únicos, etc.)
                return 'duplicado';
            }
            return false;
        }
    }

    public function Leer(){
        include 'Conexion.php';
        $stmt = $pdo->prepare("SELECT * FROM registros");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUsuarioRegistrado($correo, $lada, $telefono){
        include 'Conexion.php';
        $stmt = $pdo->prepare("SELECT idR FROM registros WHERE correo = :correo AND lada = :lada AND telefono = :telefono");
        $stmt->execute([':correo' => $correo, ':lada' => $lada, ':telefono' => $telefono]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
     public function buscarUsuarioPorId($idR) {
        include "Conexion.php"; // $pdo debe estar definido en este archivo
        $stmt = $pdo->prepare("SELECT * FROM registros WHERE idR = :idR");
        $stmt->execute([':idR' => $idR]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método para actualizar datos del usuario
    public function modificarUsuario($idR, $nombre, $apellidos, $telefono, $correo, $origen, $pais) {
        include "Conexion.php";
        $stmt = $pdo->prepare("UPDATE registros SET nombre = :nombre, apellidos = :apellidos, telefono = :telefono, correo = :correo, origen=:origen, pais=:pais WHERE idR = :idR");
        return $stmt->execute([
            ':nombre'    => $nombre,
            ':apellidos' => $apellidos,
            ':telefono'  => $telefono,
            ':correo'    => $correo,
            ':origen'    => $origen,
            ':pais'      => $pais,
            ':idR'       => $idR
        ]);
    }
    public function eliminarEvento($idR,$idE) {
        include "Conexion.php";
        $stmt = $pdo->prepare("DELETE FROM inscripciones WHERE idR = :idR AND idE = :idE;");
        $eliminar=$stmt->execute([':idR' => $idR, ':idE' => $idE]);
        return $eliminar;
    }
    public function asistencia($idR, $idE) {
        try {
            include "Conexion.php";
            // Verificar si existe inscripción
            $sql = "SELECT asistio FROM inscripciones WHERE idR = :idR AND idE = :idE";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":idR" => $idR, ":idE" => $idE]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($registro) {
                if ($registro['asistio'] == 1) {
                    // Ya estaba marcada
                    return false;
                } else {
                    // Marcar asistencia
                    $sqlUpdate = "UPDATE inscripciones 
                                  SET asistio = 1, fecha_asistencia = NOW() 
                                  WHERE idR = :idR AND idE = :idE";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([":idR" => $idR, ":idE" => $idE]);
                    return $stmtUpdate;
                }
            } else {
                return false; // No existe inscripción con ese idR
            }
        } catch (PDOException $e) {
            error_log("Error en asistencia: " . $e->getMessage());
            return false;
        }
    }
    public function eliminarUsuario($idR) {
        include "Conexion.php";
        $stmt = $pdo->prepare("DELETE FROM registros WHERE idR = :idR");
        $eliminado=$stmt->execute([':idR' => $idR]);
        return $eliminado;
    }

}   

?>
