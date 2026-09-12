<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: horas.php");
    exit();
}

$id_beca = isset($_POST["id_beca"]) ? (int) $_POST["id_beca"] : 0;
$fecha_desde = $_POST["fecha_desde"] ?? "";
$fecha_hasta = $_POST["fecha_hasta"] ?? "";
$actividad = trim($_POST["actividad"] ?? "");
$horas = isset($_POST["horas"]) ? (int) $_POST["horas"] : 0;
$observacion = trim($_POST["observacion"] ?? "");


/* ==============================
   VALIDAR DATOS
============================== */

if (
    $id_beca <= 0 ||
    empty($fecha_desde) ||
    empty($fecha_hasta) ||
    empty($actividad) ||
    $horas <= 0
) {
    die("Faltan datos obligatorios para registrar las horas.");
}


/* ==============================
   VALIDAR RANGO DE FECHAS
============================== */

if ($fecha_hasta < $fecha_desde) {
    die("La fecha Hasta no puede ser anterior a la fecha Desde.");
}


/* ==============================
   VERIFICAR BECA
============================== */

$sql = "SELECT id_beca
        FROM becas
        WHERE id_beca = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_beca);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("La beca seleccionada no existe.");
}

$stmt->close();


/* ==============================
   REGISTRAR HORAS
============================== */

$sql = "INSERT INTO horas_beca
        (
            id_beca,
            fecha,
            fecha_desde,
            fecha_hasta,
            actividad,
            horas,
            observacion
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

/*
   La columna fecha se mantiene por compatibilidad
   con la estructura anterior.
   
   Utilizamos fecha_desde como fecha principal.
*/

$fecha = $fecha_desde;

$stmt->bind_param(
    "issssis",
    $id_beca,
    $fecha,
    $fecha_desde,
    $fecha_hasta,
    $actividad,
    $horas,
    $observacion
);


if ($stmt->execute()) {

    header(
        "Location: registrar_horas.php?id=" .
        $id_beca .
        "&guardado=1"
    );

    exit();

} else {

    die(
        "Error al registrar las horas: " .
        $stmt->error
    );

}


$stmt->close();
$conexion->close();

?>