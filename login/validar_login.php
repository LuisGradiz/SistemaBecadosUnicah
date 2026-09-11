<?php

session_start();

require_once "../config/conexion.php";

$usuario = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE usuario = ? AND estado = 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {

    $usuarioBD = $resultado->fetch_assoc();

    if ($password == $usuarioBD['password']) {

        $_SESSION['id_usuario'] = $usuarioBD['id_usuario'];
        $_SESSION['usuario'] = $usuarioBD['usuario'];
        $_SESSION['nombre'] = $usuarioBD['nombre'];

        header("Location: ../dashboard/dashboard.php");
        exit();

    } else {

        echo "Contraseña incorrecta.";

    }

} else {

    echo "Usuario no encontrado o inactivo.";

}

?>