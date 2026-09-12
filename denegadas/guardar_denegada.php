<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: denegadas.php");
    exit();
}

/* ==============================
   RECIBIR DATOS
   ============================== */

$numero_cuenta = trim($_POST['numero_cuenta'] ?? '');
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$id_tipo_beca = isset($_POST['id_tipo_beca']) ? intval($_POST['id_tipo_beca']) : 0;
$fecha = $_POST['fecha'] ?? '';
$detalle = trim($_POST['detalle'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');


/* ==============================
   VALIDAR DATOS
   ============================== */

if (
    $numero_cuenta === '' ||
    $nombre_completo === '' ||
    $id_tipo_beca <= 0 ||
    $fecha === ''
) {
    die("Error: complete todos los campos obligatorios.");
}


/* ==============================
   VERIFICAR QUE LA BECA EXISTA
   ============================== */

$sql_beca = "
    SELECT id_tipo_beca
    FROM tipos_beca
    WHERE id_tipo_beca = ?
";

$stmt_beca = $conexion->prepare($sql_beca);

if (!$stmt_beca) {
    die("Error en la consulta de la beca: " . $conexion->error);
}

$stmt_beca->bind_param("i", $id_tipo_beca);
$stmt_beca->execute();

$resultado_beca = $stmt_beca->get_result();

if ($resultado_beca->num_rows === 0) {
    die("Error: la beca seleccionada no existe.");
}

$stmt_beca->close();


/* ==============================
   INSERTAR DENEGADA
   ============================== */

$sql = "
    INSERT INTO denegadas
    (
        numero_cuenta,
        nombre_completo,
        id_tipo_beca,
        fecha,
        detalle,
        observaciones
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar el registro: " . $conexion->error);
}

$stmt->bind_param(
    "ssisss",
    $numero_cuenta,
    $nombre_completo,
    $id_tipo_beca,
    $fecha,
    $detalle,
    $observaciones
);


/* ==============================
   GUARDAR
   ============================== */

if ($stmt->execute()) {

    $stmt->close();

    header("Location: denegadas.php");
    exit();

} else {

    die("Error al registrar la denegada: " . $stmt->error);

}

?>