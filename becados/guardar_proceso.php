<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


/* ==============================
   SOLO SE PERMITE POST
   ============================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: becados.php");
    exit();
}


/* ==============================
   DATOS DEL PROCESO
   ============================== */

$id_becado     = intval($_POST['id_becado'] ?? 0);
$tipo_proceso  = trim($_POST['tipo_proceso'] ?? '');
$descripcion   = trim($_POST['descripcion'] ?? '');
$observacion   = trim($_POST['observacion'] ?? '');
$fecha_proceso = trim($_POST['fecha_proceso'] ?? '');


/* ==============================
   VALIDAR DATOS
   ============================== */

if (
    $id_becado <= 0 ||
    empty($tipo_proceso) ||
    empty($descripcion) ||
    empty($fecha_proceso)
) {

    die("Faltan datos obligatorios para registrar el proceso.");

}


/* ==============================
   VALIDAR FECHA
   ============================== */

$fecha_valida = DateTime::createFromFormat(
    'Y-m-d',
    $fecha_proceso
);

if (
    !$fecha_valida ||
    $fecha_valida->format('Y-m-d') !== $fecha_proceso
) {

    die("La fecha del proceso no es válida.");

}


/* ==============================
   VERIFICAR QUE EXISTA EL BECADO
   ============================== */

$sql_becado = "
    SELECT id_becado
    FROM becados
    WHERE id_becado = ?
";

$stmt_becado = $conexion->prepare($sql_becado);

if (!$stmt_becado) {
    die("Error al preparar la consulta del becado.");
}

$stmt_becado->bind_param(
    "i",
    $id_becado
);

$stmt_becado->execute();

$resultado = $stmt_becado->get_result();

if ($resultado->num_rows === 0) {

    $stmt_becado->close();

    die("El becado seleccionado no existe.");

}

$stmt_becado->close();


/* ==============================
   USUARIO QUE REGISTRA
   ============================== */

$usuario_registro = $_SESSION['usuario'] ?? null;


/* ==============================
   GUARDAR EN HISTORIAL
   ============================== */

$sql = "
    INSERT INTO historial_becados
    (
        id_becado,
        tipo_proceso,
        descripcion,
        observacion,
        fecha_proceso,
        usuario_registro
    )
    VALUES (?, ?, ?, ?, ?, ?)
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {

    die(
        "Error al preparar el registro del proceso: " .
        $conexion->error
    );

}


$stmt->bind_param(
    "isssss",
    $id_becado,
    $tipo_proceso,
    $descripcion,
    $observacion,
    $fecha_proceso,
    $usuario_registro
);


if (!$stmt->execute()) {

    die(
        "Error al guardar el proceso: " .
        htmlspecialchars($stmt->error)
    );

}


$stmt->close();


/* ==============================
   REGRESAR A VER BECADO
   ============================== */

header(
    "Location: ver_becado.php?id=" .
    $id_becado
);

exit();

?>