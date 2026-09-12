<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: becados.php");
    exit();
}


/* =========================================================
   DATOS RECIBIDOS
   ========================================================= */

$id_becado = intval($_POST['id_becado'] ?? 0);

$numero_cuenta   = trim($_POST['numero_cuenta'] ?? '');
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$id_carrera      = intval($_POST['id_carrera'] ?? 0);
$telefono        = trim($_POST['telefono'] ?? '');
$correo          = trim($_POST['correo'] ?? '');
$id_estado       = intval($_POST['id_estado'] ?? 0);
$id_tipo_beca    = intval($_POST['id_tipo_beca'] ?? 0);
$observaciones   = trim($_POST['observaciones'] ?? '');


if (
    $id_becado <= 0 ||
    empty($numero_cuenta) ||
    empty($nombre_completo) ||
    $id_carrera <= 0 ||
    $id_estado <= 0 ||
    $id_tipo_beca <= 0
) {
    die("Faltan datos obligatorios.");
}


/* =========================================================
   TRANSACCIÓN
   ========================================================= */

$conexion->begin_transaction();

try {


    /* =====================================================
       OBTENER DATOS ACTUALES
       ===================================================== */

    $sql_actual = "
        SELECT
            b.id_carrera,
            be.id_beca,
            be.id_tipo_beca,
            be.id_estado
        FROM becados b
        LEFT JOIN becas be
            ON b.id_becado = be.id_becado
        WHERE b.id_becado = ?
        ORDER BY be.id_beca DESC
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql_actual);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param("i", $id_becado);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        throw new Exception("El becado no existe.");
    }

    $actual = $resultado->fetch_assoc();

    $id_carrera_anterior = intval($actual['id_carrera']);
    $id_beca_anterior    = intval($actual['id_beca']);
    $id_tipo_anterior    = intval($actual['id_tipo_beca']);

    $stmt->close();


    /* =====================================================
       OBTENER PERÍODO DE INICIO ACTUAL
       
       IMPORTANTE:
       Se obtiene ANTES de modificar los períodos.
       No se utiliza el año actual.
       ===================================================== */

    $periodo_inicio_numero = 0;
    $periodo_inicio_anio = 0;

    if ($id_beca_anterior > 0) {

        $sql_periodo_inicio = "
            SELECT
                numero_periodo,
                anio
            FROM periodos_beca
            WHERE id_beca = ?
            ORDER BY
                anio ASC,
                numero_periodo ASC
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sql_periodo_inicio);

        if (!$stmt) {
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "i",
            $id_beca_anterior
        );

        $stmt->execute();

        $res = $stmt->get_result();

        if ($res->num_rows > 0) {

            $periodo_inicio = $res->fetch_assoc();

            $periodo_inicio_numero =
                intval($periodo_inicio['numero_periodo']);

            $periodo_inicio_anio =
                intval($periodo_inicio['anio']);
        }

        $stmt->close();
    }


    /* =====================================================
       NOMBRES DE CARRERAS
       ===================================================== */

    $nombre_carrera_anterior = "Sin carrera";
    $nombre_carrera_nueva = "Sin carrera";

    $sql = "
        SELECT nombre_carrera
        FROM carreras
        WHERE id_carrera = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param(
        "i",
        $id_carrera_anterior
    );

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res->num_rows > 0) {

        $nombre_carrera_anterior =
            $res->fetch_assoc()['nombre_carrera'];
    }

    $stmt->close();


    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param(
        "i",
        $id_carrera
    );

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res->num_rows > 0) {

        $nombre_carrera_nueva =
            $res->fetch_assoc()['nombre_carrera'];
    }

    $stmt->close();


    /* =====================================================
       NOMBRES DE BECAS
       ===================================================== */

    $nombre_beca_anterior = "Beca anterior";
    $nombre_beca_nueva = "Nueva beca";

    $sql = "
        SELECT nombre_beca
        FROM tipos_beca
        WHERE id_tipo_beca = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param(
        "i",
        $id_tipo_anterior
    );

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res->num_rows > 0) {

        $nombre_beca_anterior =
            $res->fetch_assoc()['nombre_beca'];
    }

    $stmt->close();


    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param(
        "i",
        $id_tipo_beca
    );

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res->num_rows > 0) {

        $nombre_beca_nueva =
            $res->fetch_assoc()['nombre_beca'];
    }

    $stmt->close();


    /* =====================================================
       ACTUALIZAR DATOS DEL BECADO
       ===================================================== */

    $sql = "
        UPDATE becados
        SET
            numero_cuenta = ?,
            nombre_completo = ?,
            id_carrera = ?,
            telefono = ?,
            correo = ?,
            id_estado = ?,
            observaciones = ?
        WHERE id_becado = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param(
        "ssissisi",
        $numero_cuenta,
        $nombre_completo,
        $id_carrera,
        $telefono,
        $correo,
        $id_estado,
        $observaciones,
        $id_becado
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();


    /* =====================================================
       CAMBIO DE TIPO DE BECA
       ===================================================== */

    if ($id_tipo_anterior != $id_tipo_beca) {


        /* ---------------------------------------------
           CREAR NUEVA BECA
           --------------------------------------------- */

        $detalle = "Beca actual por cambio de tipo de beca";

        $sql = "
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

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "iiiss",
            $id_becado,
            $id_tipo_beca,
            $id_estado,
            $detalle,
            $observaciones
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $id_beca_nueva = $conexion->insert_id;

        $stmt->close();


        /* ---------------------------------------------
           PASAR HORAS A LA NUEVA BECA
           --------------------------------------------- */

        if ($id_beca_anterior > 0) {

            $sql = "
                UPDATE horas_beca
                SET id_beca = ?
                WHERE id_beca = ?
            ";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {
                throw new Exception($conexion->error);
            }

            $stmt->bind_param(
                "ii",
                $id_beca_nueva,
                $id_beca_anterior
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $stmt->close();
        }


        /* ---------------------------------------------
           COPIAR LOS PERÍODOS
           --------------------------------------------- */

        if ($id_beca_anterior > 0) {

            $sql = "
                INSERT INTO periodos_beca
                (
                    id_beca,
                    numero_periodo,
                    anio
                )
                SELECT
                    ?,
                    numero_periodo,
                    anio
                FROM periodos_beca
                WHERE id_beca = ?
            ";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {
                throw new Exception($conexion->error);
            }

            $stmt->bind_param(
                "ii",
                $id_beca_nueva,
                $id_beca_anterior
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $stmt->close();
        }


        /* ---------------------------------------------
           MARCAR ANTERIOR COMO HISTÓRICA
           --------------------------------------------- */

        $detalle_anterior =
            "Beca finalizada por cambio de tipo de beca";

        $sql = "
            UPDATE becas
            SET detalle = ?
            WHERE id_beca = ?
        ";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "si",
            $detalle_anterior,
            $id_beca_anterior
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();


        /* ---------------------------------------------
           NUEVA BECA ACTIVA
           --------------------------------------------- */

        $id_beca_activa = $id_beca_nueva;
    }

    else {

        $id_beca_activa = $id_beca_anterior;
    }


    /* =====================================================
       CAMBIO DE CARRERA
       
       IMPORTANTE:
       Se conserva el período de inicio original.
       SOLO cambia la cantidad de períodos según
       la nueva carrera.
       ===================================================== */

    if ($id_carrera_anterior != $id_carrera) {


        /* ---------------------------------------------
           VERIFICAR QUE EXISTAN LOS PERÍODOS ANTERIORES
           --------------------------------------------- */

        if (
            $periodo_inicio_numero <= 0 ||
            $periodo_inicio_anio <= 0
        ) {

            throw new Exception(
                "No se encontró el período de inicio de la beca actual."
            );
        }


        /* ---------------------------------------------
           OBTENER CANTIDAD DE PERÍODOS
           DE LA NUEVA CARRERA
           --------------------------------------------- */

        $periodos_nueva_carrera = 0;

        $sql = "
            SELECT periodos
            FROM carreras
            WHERE id_carrera = ?
        ";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "i",
            $id_carrera
        );

        $stmt->execute();

        $res = $stmt->get_result();

        if ($res->num_rows === 0) {

            throw new Exception(
                "No se encontró la nueva carrera."
            );
        }

        $fila = $res->fetch_assoc();

        $periodos_nueva_carrera =
            intval($fila['periodos']);

        $stmt->close();


        if ($periodos_nueva_carrera <= 0) {

            throw new Exception(
                "La nueva carrera no tiene períodos configurados."
            );
        }


        /* ---------------------------------------------
           ELIMINAR PERÍODOS SOLO DE LA BECA ACTIVA
           --------------------------------------------- */

        if ($id_beca_activa > 0) {

            $sql = "
                DELETE FROM periodos_beca
                WHERE id_beca = ?
            ";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {
                throw new Exception($conexion->error);
            }

            $stmt->bind_param(
                "i",
                $id_beca_activa
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $stmt->close();


            /* -----------------------------------------
               CREAR NUEVOS PERÍODOS
               
               COMIENZA DESDE EL PERÍODO ORIGINAL
               ----------------------------------------- */

            $numero_periodo =
                $periodo_inicio_numero;

            $anio =
                $periodo_inicio_anio;


            $sql = "
                INSERT INTO periodos_beca
                (
                    id_beca,
                    numero_periodo,
                    anio
                )
                VALUES (?, ?, ?)
            ";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {
                throw new Exception($conexion->error);
            }


            for (
                $i = 1;
                $i <= $periodos_nueva_carrera;
                $i++
            ) {

                $stmt->bind_param(
                    "iii",
                    $id_beca_activa,
                    $numero_periodo,
                    $anio
                );

                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }


                /* -------------------------------------
                   SIGUIENTE PERÍODO ACADÉMICO

                   1 → 2 → 3 → 1 del siguiente año
                   ------------------------------------- */

                $numero_periodo++;

                if ($numero_periodo > 3) {

                    $numero_periodo = 1;

                    $anio++;
                }
            }

            $stmt->close();
        }


        /* ---------------------------------------------
           HISTORIAL DE CAMBIO DE CARRERA
           --------------------------------------------- */

        $tipo_proceso = "Cambio de carrera";

        $descripcion =
            "Se cambió de " .
            $nombre_carrera_anterior .
            " a " .
            $nombre_carrera_nueva;

        $observacion_proceso =
            !empty($observaciones)
            ? $observaciones
            : "Cambio de carrera registrado.";

        $fecha_proceso = date("Y-m-d");

        $usuario_registro =
            $_SESSION['usuario'] ?? 'admin';


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
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "isssss",
            $id_becado,
            $tipo_proceso,
            $descripcion,
            $observacion_proceso,
            $fecha_proceso,
            $usuario_registro
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
    }


    /* =====================================================
       HISTORIAL DE CAMBIO DE BECA
       ===================================================== */

    if ($id_tipo_anterior != $id_tipo_beca) {

        $tipo_proceso = "Cambio de beca";

        $descripcion =
            "Se cambió de " .
            $nombre_beca_anterior .
            " a " .
            $nombre_beca_nueva;

        $observacion_proceso =
            !empty($observaciones)
            ? $observaciones
            : "Cambio de beca registrado.";

        $fecha_proceso = date("Y-m-d");

        $usuario_registro =
            $_SESSION['usuario'] ?? 'admin';


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
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "isssss",
            $id_becado,
            $tipo_proceso,
            $descripcion,
            $observacion_proceso,
            $fecha_proceso,
            $usuario_registro
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
    }


    /* =====================================================
       SI NO CAMBIÓ LA BECA
       ACTUALIZAR ESTADO Y OBSERVACIONES
       ===================================================== */

    if (
        $id_tipo_anterior == $id_tipo_beca &&
        $id_beca_activa > 0
    ) {

        $sql = "
            UPDATE becas
            SET
                id_estado = ?,
                observaciones = ?
            WHERE id_beca = ?
        ";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception($conexion->error);
        }

        $stmt->bind_param(
            "isi",
            $id_estado,
            $observaciones,
            $id_beca_activa
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
    }


    /* =====================================================
       GUARDAR CAMBIOS
       ===================================================== */

    $conexion->commit();

    header(
        "Location: ver_becado.php?id=" .
        $id_becado
    );

    exit();


} catch (Exception $e) {

    $conexion->rollback();

    die(
        "Error al actualizar el becado: " .
        htmlspecialchars($e->getMessage())
    );
}

?>