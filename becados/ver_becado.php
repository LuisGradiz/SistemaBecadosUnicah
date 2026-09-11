<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


// ======================================================
// VALIDAR ID DEL BECADO
// ======================================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: becados.php");
    exit();

}

$id_becado = intval($_GET['id']);


// ======================================================
// DATOS DEL BECADO
// ======================================================

$sql_becado = "SELECT

                    b.id_becado,
                    b.numero_cuenta,
                    b.nombre_completo,
                    b.telefono,
                    b.correo,
                    b.observaciones,

                    c.nombre_carrera,
                    c.periodos,

                    e.nombre_estado

                FROM becados b

                LEFT JOIN carreras c
                    ON b.id_carrera = c.id_carrera

                LEFT JOIN estados e
                    ON b.id_estado = e.id_estado

                WHERE b.id_becado = ?";


$stmt_becado =
    $conexion->prepare($sql_becado);


$stmt_becado->bind_param(
    "i",
    $id_becado
);


$stmt_becado->execute();


$resultado_becado =
    $stmt_becado->get_result();


if (
    !$resultado_becado ||
    $resultado_becado->num_rows == 0
) {

    echo "Becado no encontrado.";
    exit();

}


$becado =
    $resultado_becado->fetch_assoc();


$stmt_becado->close();


// ======================================================
// HISTORIAL DE BECAS
// ======================================================

$sql_becas = "
    SELECT
        be.id_beca,
        tb.nombre_beca,
        e.nombre_estado,
        be.detalle,
        be.observaciones,

        (
            SELECT CONCAT(
                pb.numero_periodo,
                ' período ',
                pb.anio
            )
            FROM periodos_beca pb
            WHERE pb.id_beca = be.id_beca
            ORDER BY pb.anio ASC, pb.numero_periodo ASC
            LIMIT 1
        ) AS periodo_inicio,

        (
            SELECT CONCAT(
                pb.numero_periodo,
                ' período ',
                pb.anio
            )
            FROM periodos_beca pb
            WHERE pb.id_beca = be.id_beca
            ORDER BY pb.anio DESC, pb.numero_periodo DESC
            LIMIT 1
        ) AS periodo_fin

    FROM becas be

    INNER JOIN tipos_beca tb
        ON be.id_tipo_beca = tb.id_tipo_beca

    INNER JOIN estados e
        ON be.id_estado = e.id_estado

    WHERE be.id_becado = ?

    ORDER BY be.id_beca DESC

    LIMIT 1
";

$stmt_becas = $conexion->prepare($sql_becas);
$stmt_becas->bind_param("i", $id_becado);
$stmt_becas->execute();

$resultado_becas = $stmt_becas->get_result();


// ======================================================
// HISTORIAL DE PROCESOS
// ======================================================

$sql_historial = "SELECT

                        id_historial,
                        tipo_proceso,
                        descripcion,
                        observacion,
                        fecha_proceso,
                        usuario_registro

                    FROM historial_becados

                    WHERE id_becado = ?

                    ORDER BY
                        fecha_proceso DESC,
                        id_historial DESC";


$stmt_historial =
    $conexion->prepare($sql_historial);


$stmt_historial->bind_param(
    "i",
    $id_becado
);


$stmt_historial->execute();


$resultado_historial =
    $stmt_historial->get_result();


// ======================================================
// RESUMEN DE HORAS
// ======================================================
// IMPORTANTE:
//
// Aquí solamente mostramos la BECA ACTUAL.
//
// La beca actual es la última beca registrada
// para este becado.
//
// Las horas ya fueron trasladadas por
// guardar_edicion.php a esta nueva beca.
//
// Por eso no debemos mostrar las becas anteriores.
// ======================================================

$sql_horas = "SELECT

                    be.id_beca,

                    tb.nombre_beca,

                    tb.horas_requeridas,

                    COALESCE(

                        (
                            SELECT SUM(hb.horas)

                            FROM horas_beca hb

                            WHERE hb.id_beca =
                                  be.id_beca
                        ),

                        0

                    ) AS horas_realizadas


                FROM becas be

                INNER JOIN tipos_beca tb

                    ON be.id_tipo_beca =
                       tb.id_tipo_beca

                WHERE be.id_becado = ?

                AND tb.requiere_horas = 1

                AND be.id_beca = (

                    SELECT MAX(b2.id_beca)

                    FROM becas b2

                    WHERE b2.id_becado =
                          be.id_becado

                )";


$stmt_horas =
    $conexion->prepare($sql_horas);


$stmt_horas->bind_param(
    "i",
    $id_becado
);


$stmt_horas->execute();


$resultado_horas =
    $stmt_horas->get_result();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Ver becado - Sistemas Becados UNICAH
    </title>


    <style>

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;

            font-family: Arial, sans-serif;

        }


        body {

            background-color: #f4f6f9;

        }


        .contenedor {

            display: flex;

            min-height: 100vh;

        }


        /* ==================================================
           MENU
        ================================================== */

        .menu {

            width: 240px;

            background-color: #1f2937;

            color: white;

            padding: 25px 15px;

        }


        .menu h2 {

            text-align: center;

            margin-bottom: 30px;

            font-size: 20px;

        }


        .menu a {

            display: block;

            color: white;

            text-decoration: none;

            padding: 14px;

            margin-bottom: 8px;

            border-radius: 6px;

        }


        .menu a:hover {

            background-color: #374151;

        }


        .menu .activo {

            background-color: #374151;

        }


        .cerrar {

            margin-top: 30px;

            background-color: #b91c1c;

        }


        .cerrar:hover {

            background-color: #991b1b !important;

        }


        /* ==================================================
           CONTENIDO
        ================================================== */

        .contenido {

            flex: 1;

            padding: 30px;

        }


        .encabezado {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .encabezado h1 {

            color: #1f2937;

        }


        .usuario {

            background-color: white;

            padding: 10px 15px;

            border-radius: 8px;

            box-shadow:
                0 2px 6px rgba(0,0,0,0.08);

        }


        /* ==================================================
           BOTONES
        ================================================== */

        .botones-superiores {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 25px;

        }


        .volver {

            display: inline-block;

            color: white;

            background-color: #374151;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 6px;

        }


        .volver:hover {

            background-color: #1f2937;

        }


        .editar {

            display: inline-block;

            color: white;

            background-color: #166534;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 6px;

        }


        .editar:hover {

            background-color: #14532d;

        }


        .nuevo-proceso {

            display: inline-block;

            color: white;

            background-color: #1d4ed8;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 6px;

            margin-left: auto;

        }


        .nuevo-proceso:hover {

            background-color: #1e40af;

        }


        /* ==================================================
           TARJETAS
        ================================================== */

        .tarjeta {

            background-color: white;

            padding: 28px;

            border-radius: 10px;

            margin-bottom: 25px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .tarjeta h2 {

            color: #1f2937;

            margin-bottom: 20px;

            border-bottom:
                1px solid #e5e7eb;

            padding-bottom: 15px;

        }


        /* ==================================================
           DATOS PERSONALES
        ================================================== */

        .datos {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 25px;

        }


        .dato label {

            display: block;

            color: #6b7280;

            font-weight: bold;

            margin-bottom: 7px;

        }


        .dato span {

            color: #111827;

            font-size: 17px;

        }


        /* ==================================================
           TABLAS
        ================================================== */

        .tabla-contenedor {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background-color: #1f2937;

            color: white;

            padding: 14px;

            text-align: left;

        }


        td {

            padding: 13px;

            border-bottom:
                1px solid #e5e7eb;

            vertical-align: top;

        }


        tr:hover {

            background-color: #f9fafb;

        }


        .sin-registros {

            text-align: center;

            padding: 30px;

            color: #6b7280;

        }


        /* ==================================================
           BECAS
        ================================================== */

        .historial-actual {

            background-color: #f0fdf4;

        }


        .historial-actual td:first-child {

            border-left:
                4px solid #166534;

        }


        /* ==================================================
           HISTORIAL DE PROCESOS
        ================================================== */

        .proceso-fecha {

            white-space: nowrap;

            font-weight: bold;

            color: #374151;

        }


        .proceso-tipo {

            font-weight: bold;

            color: #1f2937;

        }


        .proceso-descripcion {

            color: #374151;

            margin-bottom: 7px;

        }


        .proceso-observacion {

            color: #6b7280;

            font-size: 14px;

            line-height: 1.5;

        }


        .proceso-observacion strong {

            color: #374151;

        }


        .proceso-usuario {

            color: #9ca3af;

            font-size: 12px;

            margin-top: 8px;

        }


        /* ==================================================
           HORAS
        ================================================== */

        .horas-completadas {

            font-weight: bold;

            color: #166534;

        }


        .horas-pendientes {

            font-weight: bold;

            color: #b91c1c;

        }


        .barra-horas {

            width: 180px;

            height: 10px;

            background-color: #e5e7eb;

            border-radius: 10px;

            overflow: hidden;

        }


        .progreso-horas {

            height: 100%;

            background-color: #166534;

        }


        .porcentaje {

            margin-top: 5px;

            font-size: 13px;

            color: #6b7280;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 800px) {

            .menu {

                width: 200px;

            }


            .datos {

                grid-template-columns: 1fr;

            }


            .encabezado {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }


            .botones-superiores {

                flex-wrap: wrap;

            }


            .nuevo-proceso {

                margin-left: 0;

            }

        }

    </style>

</head>


<body>


<div class="contenedor">


    <!-- ==================================================
         MENU
    ================================================== -->

    <aside class="menu">

        <h2>
            Sistemas Becados
        </h2>


        <a href="../dashboard/dashboard.php">
            Inicio
        </a>


        <a
            href="becados.php"
            class="activo"
        >
            Becados
        </a>


        <a href="../horas/horas.php">
            Horas Beca
        </a>


        <a href="../denegadas/denegadas.php">
            Denegadas
        </a>


        <a href="../documentos/documentos.php">
            Documentación
        </a>


        <a href="../reportes/reportes.php">
            Reportes
        </a>


        <a
            href="../login/logout.php"
            class="cerrar"
        >
            Cerrar sesión
        </a>

    </aside>


    <!-- ==================================================
         CONTENIDO
    ================================================== -->

    <main class="contenido">


        <!-- ENCABEZADO -->

        <div class="encabezado">

            <h1>
                Información del becado
            </h1>


            <div class="usuario">

                Bienvenido,

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION['nombre']
                    );

                    ?>

                </strong>

            </div>

        </div>


        <!-- ==================================================
             BOTONES
        ================================================== -->

        <div class="botones-superiores">

            <a
                href="becados.php"
                class="volver"
            >
                ← Volver a becados
            </a>


            <a
                href="editar_becado.php?id=<?php echo $id_becado; ?>"
                class="editar"
            >
                Editar becado
            </a>


            <a
                href="registrar_proceso.php?id=<?php echo $id_becado; ?>"
                class="nuevo-proceso"
            >
                + Registrar proceso
            </a>

        </div>


        <!-- ==================================================
             DATOS PERSONALES
        ================================================== -->

        <div class="tarjeta">

            <h2>
                Datos personales
            </h2>


            <div class="datos">


                <div class="dato">

                    <label>
                        Número de cuenta
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['numero_cuenta']
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Nombre completo
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['nombre_completo']
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Carrera
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['nombre_carrera']
                            ?? 'Sin carrera'
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Estado
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['nombre_estado']
                            ?? 'Sin estado'
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Teléfono
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['telefono']
                            ?? 'No registrado'
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Correo electrónico
                    </label>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $becado['correo']
                            ?? 'No registrado'
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Períodos de la carrera
                    </label>

                    <span>

                        <?php

                        if (
                            $becado['periodos']
                            !== null
                        ) {

                            echo htmlspecialchars(
                                $becado['periodos']
                            );

                            echo " períodos";

                        } else {

                            echo "No registrado";

                        }

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <label>
                        Observaciones
                    </label>

                    <span>

                        <?php

                        if (
                            !empty(
                                $becado['observaciones']
                            )
                        ) {

                            echo htmlspecialchars(
                                $becado['observaciones']
                            );

                        } else {

                            echo "Sin observaciones";

                        }

                        ?>

                    </span>

                </div>


            </div>

        </div>


        <!-- ==================================================
             HISTORIAL DE BECAS
        ================================================== -->

        <div class="tarjeta">

            <h2>
                Historial de becas
            </h2>


            <div class="tabla-contenedor">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Tipo de beca
                            </th>

                            <th>
                                Período de inicio
                            </th>

                            <th>
                                Período de finalización
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Detalle
                            </th>

                        </tr>

                    </thead>

                    <tbody>

<?php if ($resultado_becas->num_rows > 0): ?>

    <?php $beca = $resultado_becas->fetch_assoc(); ?>

    <tr class="historial-actual">

        <td>
            <strong>
                <?= htmlspecialchars($beca['nombre_beca']) ?>
            </strong>
        </td>

        <td>
            <?= htmlspecialchars(
                $beca['periodo_inicio'] ?? 'Sin período'
            ) ?>
        </td>

        <td>
            <?= htmlspecialchars(
                $beca['periodo_fin'] ?? 'Sin período'
            ) ?>
        </td>

        <td>
            <?= htmlspecialchars(
                $beca['nombre_estado'] ?? 'Sin estado'
            ) ?>
        </td>

        <td>
            <?= htmlspecialchars(
                $beca['detalle'] ?? 'Sin detalle'
            ) ?>
        </td>

    </tr>

<?php else: ?>

    <tr>
        <td colspan="5" class="sin-registros">
            Este becado no tiene una beca registrada.
        </td>
    </tr>

<?php endif; ?>

</tbody>
                    



                </table>

            </div>

        </div>


        <!-- ==================================================
             HISTORIAL DE PROCESOS
        ================================================== -->

        <div class="tarjeta">

            <h2>
                Historial de procesos
            </h2>


            <div class="tabla-contenedor">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Tipo de proceso
                            </th>

                            <th>
                                Descripción
                            </th>

                            <th>
                                Observación
                            </th>

                            <th>
                                Registrado por
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $resultado_historial &&
                        $resultado_historial->num_rows > 0
                    ) {


                        while (
                            $proceso =
                            $resultado_historial->fetch_assoc()
                        ) {

                    ?>


                        <tr>


                            <td>

                                <div
                                    class="proceso-fecha"
                                >

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $proceso[
                                                'fecha_proceso'
                                            ]
                                        )
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <div
                                    class="proceso-tipo"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $proceso[
                                            'tipo_proceso'
                                        ]
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <div
                                    class="proceso-descripcion"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $proceso[
                                            'descripcion'
                                        ]
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <div
                                    class="proceso-observacion"
                                >

                                    <?php

                                    if (
                                        !empty(
                                            $proceso[
                                                'observacion'
                                            ]
                                        )
                                    ) {

                                    ?>

                                        <strong>
                                            Observación:
                                        </strong>

                                        <br>

                                        <?php

                                        echo htmlspecialchars(
                                            $proceso[
                                                'observacion'
                                            ]
                                        );

                                    } else {

                                        echo "Sin observación";

                                    }

                                    ?>

                                </div>

                            </td>


                            <td>

                                <div
                                    class="proceso-usuario"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $proceso[
                                            'usuario_registro'
                                        ]
                                        ?? 'Sistema'
                                    );

                                    ?>

                                </div>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="5"
                                class="sin-registros"
                            >

                                No hay procesos registrados
                                para este becado.

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- ==================================================
             RESUMEN DE HORAS
        ================================================== -->


        <?php

        if (
            $resultado_horas &&
            $resultado_horas->num_rows > 0
        ) {

        ?>


        <div class="tarjeta">

            <h2>
                Resumen de horas
            </h2>


            <div class="tabla-contenedor">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Tipo de beca
                            </th>

                            <th>
                                Horas requeridas
                            </th>

                            <th>
                                Horas realizadas
                            </th>

                            <th>
                                Horas pendientes
                            </th>

                            <th>
                                Progreso
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    while (
                        $horas =
                        $resultado_horas->fetch_assoc()
                    ) {


                        $requeridas =
                            intval(
                                $horas[
                                    'horas_requeridas'
                                ]
                            );


                        $realizadas =
                            floatval(
                                $horas[
                                    'horas_realizadas'
                                ]
                            );


                        $pendientes =
                            $requeridas -
                            $realizadas;


                        if (
                            $pendientes < 0
                        ) {

                            $pendientes = 0;

                        }


                        if (
                            $requeridas > 0
                        ) {

                            $porcentaje =
                                (
                                    $realizadas /
                                    $requeridas
                                ) * 100;

                        } else {

                            $porcentaje = 0;

                        }


                        if (
                            $porcentaje > 100
                        ) {

                            $porcentaje = 100;

                        }


                    ?>


                        <tr>


                            <td>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $horas[
                                            'nombre_beca'
                                        ]
                                    );

                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php

                                echo $requeridas;

                                ?>

                                horas

                            </td>


                            <td
                                class="horas-completadas"
                            >

                                <?php

                                echo $realizadas;

                                ?>

                                horas

                            </td>


                            <td
                                class="horas-pendientes"
                            >

                                <?php

                                echo $pendientes;

                                ?>

                                horas

                            </td>


                            <td>

                                <div
                                    class="barra-horas"
                                >

                                    <div
                                        class="progreso-horas"
                                        style="
                                            width:
                                            <?php
                                            echo $porcentaje;
                                            ?>%
                                        "
                                    ></div>

                                </div>


                                <div
                                    class="porcentaje"
                                >

                                    <?php

                                    echo number_format(
                                        $porcentaje,
                                        1
                                    );

                                    ?>%

                                </div>

                            </td>


                        </tr>


                    <?php

                    }


                    ?>


                    </tbody>

                </table>

            </div>

        </div>


        <?php

        }


        ?>


    </main>

</div>


</body>

</html>