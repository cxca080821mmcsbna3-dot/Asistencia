<?php
session_start();

// 🔐 Cerrar SOLO sesión de administrador
unset($_SESSION['idAdmin']);

// Seguridad
session_regenerate_id(true);

header("Location: ../../index.php");
exit();
