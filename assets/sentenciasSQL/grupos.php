<?php

class Grupos {

    public function crearGrupo($idGrupo, $nombre, $descripcion, $tutor) {
        include "Conexion.php";
        $stmt = $pdo->prepare("INSERT INTO grupo (idGrupo, nombre, descripcion, tutor) 
                               VALUES (:idGrupo, :nombre, :descripcion, :tutor)");
        try {
            $alta = $stmt->execute([
                ':idGrupo'    => $idGrupo,
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion,
                ':tutor' => $tutor
            ]);
            return $alta;
        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                // clave duplicada
                return 'duplicado';
            } else {
                return false; // Otro error
            }
        }
    }
    public function eliminarGrupo($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("DELETE FROM grupo WHERE idGrupo = :idGrupo");
        return $stmt->execute([':idGrupo' => $idGrupo]);
    }
    public function leerGrupos() {
        include "Conexion.php";
        $stmt = $pdo->prepare("SELECT * FROM grupo ORDER BY idGrupo ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
