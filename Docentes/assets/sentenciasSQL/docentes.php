<?php  
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
?>
