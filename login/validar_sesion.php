<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {

    header("Location: /SistemaBecadosUNICAH/login/login.php");
    exit();

}

?>