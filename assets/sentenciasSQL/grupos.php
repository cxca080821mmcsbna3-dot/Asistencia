<?php

class Grupos {

    public function crearGrupo($idGrupo, $nombre, $descripcion, $tutor) {
        include "Conexion.php";
        $stmt = $pdo->prepare("INSERT INTO grupo (idGrupo, nombre, descripcion, tutor) 
                               VALUES (:idGrupo, :nombre, :descripcion, :tutor)");
        try {
            $alta = $stmt->execute([
                ':idGrupo'    => $idGrupo,
                ':nombre'     => $nombre,
                ':descripcion'=> $descripcion,
                ':tutor'      => $tutor
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

    public function leerGrupos() {
        include "Conexion.php";
        $stmt = $pdo->prepare("SELECT * FROM grupo ORDER BY nombre ASC");
        $stmt->execute();
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $grupos;
    }

    public function inscribirUsuario($idGrupo, $idUsuario) {
        include "Conexion.php";

        // Verificar si ya está inscrito
        $stmt = $pdo->prepare("SELECT * FROM inscripciones_grupos WHERE idGrupo = :idGrupo AND idR = :idUsuario");
        $stmt->execute([':idGrupo' => $idGrupo, ':idUsuario' => $idUsuario]);
        $yaExiste = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($yaExiste) {
            return 'duplicado';
        }

        // Insertar inscripción
        $stmt = $pdo->prepare("INSERT INTO inscripciones_grupos (idGrupo, idR) VALUES (:idGrupo, :idUsuario)");
        $ok = $stmt->execute([':idGrupo' => $idGrupo, ':idUsuario' => $idUsuario]);

        return $ok ? 'true' : 'false';
    }

    public function verInscritos($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("
            SELECT r.idR, r.nombre, r.apellidos, r.lada, r.telefono, r.correo, r.medioE, r.origen, r.pais
            FROM inscripciones_grupos i
            JOIN registros r ON i.idR = r.idR
            WHERE i.idGrupo = :idGrupo
        ");
        $stmt->execute([':idGrupo' => $idGrupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerGruposUsuario($idR) {
        include "Conexion.php";
        $stmt = $pdo->prepare("
            SELECT g.*
            FROM grupo g
            INNER JOIN inscripciones_grupos i ON g.idGrupo = i.idGrupo
            WHERE i.idR = :idR
            ORDER BY g.nombre ASC
        ");
        $stmt->execute([':idR' => $idR]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerGrupoPorId($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("SELECT * FROM grupo WHERE idGrupo = :idGrupo LIMIT 1");
        $stmt->execute([':idGrupo' => $idGrupo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarGrupo($idGrupo, $nombre, $descripcion, $tutor) {
        include "Conexion.php";
        $stmt = $pdo->prepare("UPDATE grupo 
                               SET nombre = :nombre, 
                                   descripcion = :descripcion, 
                                   tutor = :tutor
                               WHERE idGrupo = :idGrupo");
        return $stmt->execute([
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':tutor'       => $tutor,
            ':idGrupo'     => $idGrupo
        ]);
    }

    public function eliminarGrupo($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("DELETE FROM grupo WHERE idGrupo = :idGrupo");
        return $stmt->execute([':idGrupo' => $idGrupo]);
    }

    // Opcional: si quieres ver asistentes (por ejemplo, usuarios activos)
    public function verAsistentes($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("
            SELECT r.idR, r.nombre, r.apellidos, r.lada, r.telefono, r.correo, i.fecha_asistencia
            FROM inscripciones_grupos i
            JOIN registros r ON i.idR = r.idR
            WHERE i.idGrupo = :idGrupo AND i.asistio = 1
        ");
        $stmt->execute([':idGrupo' => $idGrupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function estadisticasGrupo($idGrupo) {
        include "Conexion.php";
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total_inscritos, 
                   SUM(CASE WHEN asistio = 1 THEN 1 ELSE 0 END) AS total_asistentes
            FROM inscripciones_grupos
            WHERE idGrupo = :idGrupo
        ");
        $stmt->execute([':idGrupo' => $idGrupo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
?>
