<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: becados.php");
    exit();
}


/* =========================================================
   DATOS DEL BECADO
   ========================================================= */

$numero_cuenta   = trim($_POST['numero_cuenta'] ?? '');
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$id_carrera      = intval($_POST['id_carrera'] ?? 0);
$telefono        = trim($_POST['telefono'] ?? '');
$correo          = trim($_POST['correo'] ?? '');
$id_estado       = intval($_POST['id_estado'] ?? 0);
$observaciones   = trim($_POST['observaciones'] ?? '');


/* =========================================================
   DATOS DE LA BECA
   ========================================================= */

$id_tipo_beca = intval($_POST['id_tipo_beca'] ?? 0);


/* =========================================================
   PERÍODO DE INICIO
   ========================================================= */

$numero_periodo_inicio = intval($_POST['numero_periodo_inicio'] ?? 0);
$anio_inicio           = intval($_POST['anio_inicio'] ?? 0);


/* =========================================================
   VALIDACIONES
   ========================================================= */

if (
    empty($numero_cuenta) ||
    empty($nombre_completo) ||
    $id_carrera <= 0 ||
    $id_estado <= 0 ||
    $id_tipo_beca <= 0
) {

    die("Faltan datos obligatorios para registrar el becado.");

}


/*
   Validamos que el usuario realmente haya seleccionado
   el período y año de inicio.
*/

if ($numero_periodo_inicio < 1 || $numero_periodo_inicio > 3) {

    die("Debes seleccionar un período de inicio válido: 1, 2 o 3.");

}


if ($anio_inicio < 2000 || $anio_inicio > 2100) {

    die("Debes seleccionar un año de inicio válido.");

}


/* =========================================================
   INICIAR TRANSACCIÓN
   ========================================================= */

$conexion->begin_transaction();


try {


    /* =====================================================
       OBTENER DURACIÓN DE LA CARRERA
       ===================================================== */

    $sql_carrera = "
        SELECT periodos
        FROM carreras
        WHERE id_carrera = ?
    ";

    $stmt_carrera = $conexion->prepare($sql_carrera);

    if (!$stmt_carrera) {
        throw new Exception(
            "Error al preparar la consulta de la carrera."
        );
    }

    $stmt_carrera->bind_param(
        "i",
        $id_carrera
    );

    $stmt_carrera->execute();

    $resultado_carrera = $stmt_carrera->get_result();

    if ($resultado_carrera->num_rows === 0) {

        throw new Exception(
            "La carrera seleccionada no existe."
        );

    }

    $carrera = $resultado_carrera->fetch_assoc();

    $cantidad_periodos = intval($carrera['periodos']);

    $stmt_carrera->close();


    if ($cantidad_periodos <= 0) {

        throw new Exception(
            "La carrera no tiene una cantidad válida de períodos."
        );

    }


    /* =====================================================
       INSERTAR BECADO
       ===================================================== */

    $sql_becado = "
        INSERT INTO becados
        (
            numero_cuenta,
            nombre_completo,
            id_carrera,
            telefono,
            correo,
            id_estado,
            observaciones
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_becado = $conexion->prepare($sql_becado);

    if (!$stmt_becado) {

        throw new Exception(
            "Error al preparar el registro del becado."
        );

    }

    $stmt_becado->bind_param(
        "ssissis",
        $numero_cuenta,
        $nombre_completo,
        $id_carrera,
        $telefono,
        $correo,
        $id_estado,
        $observaciones
    );


    if (!$stmt_becado->execute()) {

        throw new Exception(
            "Error al registrar el becado: " .
            $stmt_becado->error
        );

    }

    $id_becado = $conexion->insert_id;

    $stmt_becado->close();


    /* =====================================================
       INSERTAR BECA
       ===================================================== */

    $sql_beca = "
        INSERT INTO becas
        (
            id_becado,
            id_tipo_beca,
            id_estado,
            detalle,
            observaciones
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt_beca = $conexion->prepare($sql_beca);

    if (!$stmt_beca) {

        throw new Exception(
            "Error al preparar el registro de la beca."
        );

    }


    $detalle_beca = "Beca registrada";


    $stmt_beca->bind_param(
        "iiiss",
        $id_becado,
        $id_tipo_beca,
        $id_estado,
        $detalle_beca,
        $observaciones
    );


    if (!$stmt_beca->execute()) {

        throw new Exception(
            "Error al registrar la beca: " .
            $stmt_beca->error
        );

    }

    $id_beca = $conexion->insert_id;

    $stmt_beca->close();


    /* =====================================================
       GENERAR PERÍODOS DE LA BECA
       ===================================================== */

    $sql_periodo = "
        INSERT INTO periodos_beca
        (
            id_beca,
            numero_periodo,
            anio,
            periodo_academico,
            estado_periodo,
            observacion
        )
        VALUES (?, ?, ?, ?, 'Pendiente', ?)
    ";

    $stmt_periodo = $conexion->prepare($sql_periodo);

    if (!$stmt_periodo) {

        throw new Exception(
            "Error al preparar los períodos de la beca."
        );

    }


    /*
       Comenzamos EXACTAMENTE desde el período
       y año que seleccionó el usuario.
    */

    $periodo_actual = $numero_periodo_inicio;
    $anio_actual    = $anio_inicio;


    /*
       Generamos tantos períodos como tenga la carrera.

       Ejemplo:

       Carrera = 12 períodos
       Inicio = 1 período 2023

       Resultado:

       1 período 2023
       2 período 2023
       3 período 2023
       1 período 2024
       2 período 2024
       3 período 2024
       ...
       3 período 2026
    */

    for ($i = 0; $i < $cantidad_periodos; $i++) {


        /* ================================================
           NOMBRE DEL PERÍODO
           ================================================ */

        $periodo_academico =
            $periodo_actual .
            " período " .
            $anio_actual;


        $observacion_periodo =
            "Período generado automáticamente";


        /* ================================================
           INSERTAR PERÍODO
           ================================================ */

        $stmt_periodo->bind_param(
            "iiiss",
            $id_beca,
            $periodo_actual,
            $anio_actual,
            $periodo_academico,
            $observacion_periodo
        );


        if (!$stmt_periodo->execute()) {

            throw new Exception(
                "Error al generar el período: " .
                $stmt_periodo->error
            );

        }


        /* ================================================
           AVANZAR AL SIGUIENTE PERÍODO
           ================================================ */

        $periodo_actual++;


        /*
           Después del 3.er período:

           3 → 1 del siguiente año
        */

        if ($periodo_actual > 3) {

            $periodo_actual = 1;

            $anio_actual++;

        }

    }


    $stmt_periodo->close();


    /* =====================================================
       CONFIRMAR TODO
       ===================================================== */

    $conexion->commit();


    header("Location: becados.php");
    exit();


} catch (Exception $e) {


    /* =====================================================
       DESHACER TODO SI OCURRIÓ UN ERROR
       ===================================================== */

    $conexion->rollback();


    die(
        "Error al registrar el becado: " .
        htmlspecialchars($e->getMessage())
    );

}

?>