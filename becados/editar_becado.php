<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


/* =====================================================
   OBTENER ID DEL BECADO
   ===================================================== */

$id_becado = intval($_GET['id'] ?? 0);

if ($id_becado <= 0) {

    die("Becado no válido.");

}


/* =====================================================
   OBTENER INFORMACIÓN DEL BECADO
   ===================================================== */

$sql = "
    SELECT
        b.id_becado,
        b.numero_cuenta,
        b.nombre_completo,
        b.id_carrera,
        b.telefono,
        b.correo,
        b.id_estado,
        b.observaciones,

        c.nombre_carrera,

        e.nombre_estado

    FROM becados b

    LEFT JOIN carreras c
        ON b.id_carrera = c.id_carrera

    LEFT JOIN estados e
        ON b.id_estado = e.id_estado

    WHERE b.id_becado = ?
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_becado
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    die("El becado no existe.");

}


$becado = $resultado->fetch_assoc();

$stmt->close();


/* =====================================================
   OBTENER BECA ACTUAL
   ===================================================== */

$sql_beca = "
    SELECT
        be.id_beca,
        be.id_tipo_beca,
        be.id_estado,
        be.detalle,
        be.observaciones,

        tb.nombre_beca

    FROM becas be

    LEFT JOIN tipos_beca tb
        ON be.id_tipo_beca = tb.id_tipo_beca

    WHERE be.id_becado = ?

    ORDER BY be.id_beca DESC

    LIMIT 1
";


$stmt_beca = $conexion->prepare($sql_beca);

$stmt_beca->bind_param(
    "i",
    $id_becado
);

$stmt_beca->execute();

$resultado_beca = $stmt_beca->get_result();

$beca = $resultado_beca->fetch_assoc();

$stmt_beca->close();


/* =====================================================
   OBTENER CARRERAS
   ===================================================== */

$carreras = $conexion->query("
    SELECT
        id_carrera,
        nombre_carrera
    FROM carreras
    ORDER BY nombre_carrera ASC
");


/* =====================================================
   OBTENER TIPOS DE BECA
   ===================================================== */

$tipos_beca = $conexion->query("
    SELECT
        id_tipo_beca,
        nombre_beca
    FROM tipos_beca
    ORDER BY nombre_beca ASC
");


/* =====================================================
   OBTENER ESTADOS
   ===================================================== */

$estados = $conexion->query("
    SELECT
        id_estado,
        nombre_estado
    FROM estados
    ORDER BY nombre_estado ASC
");

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
        Editar becado - Sistemas Becados UNICAH
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


        /* =================================================
           MENU
           ================================================= */

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


        /* =================================================
           CONTENIDO
           ================================================= */

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

            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }


        /* =================================================
           FORMULARIO
           ================================================= */

        .formulario {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }


        .seccion {
            margin-bottom: 30px;
        }


        .seccion h2 {
            color: #1f2937;

            margin-bottom: 20px;

            padding-bottom: 10px;

            border-bottom: 1px solid #e5e7eb;
        }


        .campos {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 20px;
        }


        .campo {
            display: flex;

            flex-direction: column;
        }


        .campo label {
            font-weight: bold;

            color: #374151;

            margin-bottom: 8px;
        }


        .campo input,
        .campo select,
        .campo textarea {

            padding: 11px;

            border: 1px solid #d1d5db;

            border-radius: 6px;

            font-size: 15px;

            outline: none;
        }


        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {

            border-color: #374151;
        }


        .campo textarea {

            resize: vertical;

            min-height: 100px;
        }


        .campo-completo {

            grid-column: 1 / 3;
        }


        /* =================================================
           INFORMACIÓN ACTUAL
           ================================================= */

        .informacion-actual {

            background-color: #f9fafb;

            border: 1px solid #e5e7eb;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            color: #4b5563;
        }


        .informacion-actual strong {

            color: #1f2937;
        }


        /* =================================================
           BOTONES
           ================================================= */

        .botones {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 20px;
        }


        .boton {

            padding: 12px 20px;

            border: none;

            border-radius: 6px;

            text-decoration: none;

            cursor: pointer;

            font-size: 15px;
        }


        .guardar {

            background-color: #166534;

            color: white;
        }


        .guardar:hover {

            background-color: #14532d;
        }


        .cancelar {

            background-color: #6b7280;

            color: white;
        }


        .cancelar:hover {

            background-color: #4b5563;
        }


        /* =================================================
           RESPONSIVE
           ================================================= */

        @media (max-width: 800px) {

            .menu {
                width: 200px;
            }


            .campos {

                grid-template-columns: 1fr;

            }


            .campo-completo {

                grid-column: 1;

            }

        }

    </style>

</head>


<body>


<div class="contenedor">


    <!-- =================================================
         MENU
         ================================================= -->

    <aside class="menu">

        <h2>
            Sistemas Becados
        </h2>


        <a href="../dashboard.php">
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


    <!-- =================================================
         CONTENIDO
         ================================================= -->

    <main class="contenido">


        <div class="encabezado">

            <h1>
                Editar becado
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

 
        <!-- =================================================
             FORMULARIO
             ================================================= -->

        <form
            class="formulario"
            action="guardar_edicion.php"
            method="POST"
        >


            <input
                type="hidden"
                name="id_becado"
                value="<?php echo $id_becado; ?>"
            >


            <!-- =============================================
                 INFORMACIÓN PERSONAL
                 ============================================= -->

            <div class="seccion">

                <h2>
                    Información personal
                </h2>


                <div class="campos">


                    <div class="campo">

                        <label>
                            Número de cuenta
                        </label>

                        <input
                            type="text"
                            name="numero_cuenta"
                            value="<?php
                                echo htmlspecialchars(
                                    $becado['numero_cuenta']
                                );
                            ?>"
                            required
                        >

                    </div>


                    <div class="campo">

                        <label>
                            Nombre completo
                        </label>

                        <input
                            type="text"
                            name="nombre_completo"
                            value="<?php
                                echo htmlspecialchars(
                                    $becado['nombre_completo']
                                );
                            ?>"
                            required
                        >

                    </div>


                    <div class="campo">

                        <label>
                            Teléfono
                        </label>

                        <input
                            type="text"
                            name="telefono"
                            value="<?php
                                echo htmlspecialchars(
                                    $becado['telefono'] ?? ''
                                );
                            ?>"
                        >

                    </div>


                    <div class="campo">

                        <label>
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            name="correo"
                            value="<?php
                                echo htmlspecialchars(
                                    $becado['correo'] ?? ''
                                );
                            ?>"
                        >

                    </div>


                </div>

            </div>


            <!-- =============================================
                 INFORMACIÓN ACADÉMICA
                 ============================================= -->

            <div class="seccion">

                <h2>
                    Información académica
                </h2>


                <div class="campos">


                    <div class="campo">

                        <label>
                            Carrera
                        </label>


                        <select
                            name="id_carrera"
                            required
                        >

                            <option value="">
                                Seleccione una carrera
                            </option>


                            <?php

                            while (
                                $carrera =
                                $carreras->fetch_assoc()
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $carrera['id_carrera'];
                                    ?>"
                                    <?php

                                    if (
                                        $carrera['id_carrera']
                                        ==
                                        $becado['id_carrera']
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $carrera['nombre_carrera']
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label>
                            Estado del becado
                        </label>


                        <select
                            name="id_estado"
                            required
                        >

                            <?php

                            while (
                                $estado =
                                $estados->fetch_assoc()
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $estado['id_estado'];
                                    ?>"
                                    <?php

                                    if (
                                        $estado['id_estado']
                                        ==
                                        $becado['id_estado']
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $estado['nombre_estado']
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <div class="campo campo-completo">

                        <label>
                            Observaciones
                        </label>


                        <textarea
                            name="observaciones"
                        ><?php

                            echo htmlspecialchars(
                                $becado['observaciones'] ?? ''
                            );

                        ?></textarea>

                    </div>


                </div>

            </div>


            <!-- =============================================
                 INFORMACIÓN DE LA BECA
                 ============================================= -->

            <div class="seccion">

                <h2>
                    Información de la beca
                </h2>


                <?php if ($beca) { ?>

                    <div class="informacion-actual">

                        <strong>
                            Beca actual:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $beca['nombre_beca']
                        );

                        ?>

                    </div>

                <?php } ?>


                <div class="campos">


                    <div class="campo">

                        <label>
                            Tipo de beca
                        </label>


                        <select
                            name="id_tipo_beca"
                            required
                        >

                            <option value="">
                                Seleccione un tipo de beca
                            </option>


                            <?php

                            while (
                                $tipo =
                                $tipos_beca->fetch_assoc()
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $tipo['id_tipo_beca'];
                                    ?>"
                                    <?php

                                    if (
                                        $beca &&
                                        $tipo['id_tipo_beca']
                                        ==
                                        $beca['id_tipo_beca']
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $tipo['nombre_beca']
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label>
                            Estado de la beca
                        </label>


                        <select
                            name="id_estado_beca"
                            required
                        >

                            <?php

                            /*
                             * Reiniciamos el resultado
                             * de estados.
                             */

                            $estados->data_seek(0);


                            while (
                                $estado_beca =
                                $estados->fetch_assoc()
                            ) {

                            ?>

                                <option
                                    value="<?php
                                        echo $estado_beca['id_estado'];
                                    ?>"
                                    <?php

                                    if (
                                        $beca &&
                                        $estado_beca['id_estado']
                                        ==
                                        $beca['id_estado']
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $estado_beca['nombre_estado']
                                    );

                                    ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <div class="campo campo-completo">

                        <label>
                            Observaciones de la beca
                        </label>


                        <textarea
                            name="observaciones_beca"
                        ><?php

                            if ($beca) {

                                echo htmlspecialchars(
                                    $beca['observaciones'] ?? ''
                                );

                            }

                        ?></textarea>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 BOTONES
                 ================================================= -->

            <div class="botones">


                <a
                    href="ver_becado.php?id=<?php
                        echo $id_becado;
                    ?>"
                    class="boton cancelar"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="boton guardar"
                >
                    Guardar cambios
                </button>


            </div>


        </form>


    </main>

</div>


</body>

</html>