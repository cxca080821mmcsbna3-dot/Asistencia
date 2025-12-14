<?php
session_start();

// 🔐 Cerrar SOLO la sesión del alumno
unset($_SESSION['ALUMNO']);

// Seguridad extra
session_regenerate_id(true);

header("Location: ../index.php");
exit();
