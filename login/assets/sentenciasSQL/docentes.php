<?php

class Docentes{
    public function darAltaProfe($nombre,$apellidos,$telefono,$domicilio,$correo){
        include "Conexion.php";
        try{
        $stmt = $pdo->prepare("INSERT INTO profesor (nombre,apellidos, telefono,domicilio, correo)
             VALUES(:nombre,:apellidos, :telefono, :domicilio, :correo)"
        );
        $alta= $stmt->execute([
            ':nombre'=>$nombre,
            ':apellidos'=>$apellidos,
            ':telefono'=>$telefono,
            ':domicilio'=>$domicilio,
            ':correo'=>$correo
        ]);
        return $alta;
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        return false;
    }
}
}
?>