<?php
session_start();
unset($_SESSION['DOCENTE']);
session_regenerate_id(true);

header("Location: ../index.php");
exit();
