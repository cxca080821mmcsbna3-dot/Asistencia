<?php

class Grupos {

    public function crearGrupo($idGrupo, $nombre, $descripcion, $tutor) {
        include "Conexion.php";
        $stmt = $pdo->prepare("INSERT INTO grupo (idG, nombre, descripcion, tutor) 
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
}
?>
