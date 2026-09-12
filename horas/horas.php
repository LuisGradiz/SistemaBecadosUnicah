<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

$buscar = trim($_GET['buscar'] ?? '');


/* ==================================================
   CONSULTA DE HORAS
================================================== */

$sql = "
    SELECT
        b.id_becado,
        b.numero_cuenta,
        b.nombre_completo,
        be.id_beca,
        tb.nombre_beca,
        tb.horas_requeridas,

        COALESCE(
            (
                SELECT SUM(h.horas)
                FROM horas_beca h
                WHERE h.id_beca = be.id_beca
            ),
            0
        ) AS horas_realizadas

    FROM becados b

    INNER JOIN becas be
        ON be.id_becado = b.id_becado

    INNER JOIN tipos_beca tb
        ON tb.id_tipo_beca = be.id_tipo_beca

    WHERE tb.requiere_horas = 1

    AND be.id_beca = (
        SELECT MAX(be2.id_beca)
        FROM becas be2
        WHERE be2.id_becado = b.id_becado
    )
";


/* ==================================================
   BUSCADOR
================================================== */

if ($buscar !== '') {

    $sql .= "
        AND (
            b.nombre_completo LIKE ?
            OR b.numero_cuenta LIKE ?
        )
    ";

}

$sql .= "
    ORDER BY b.nombre_completo ASC
";


$stmt = $conexion->prepare($sql);


if ($buscar !== '') {

    $busqueda = "%" . $buscar . "%";

    $stmt->bind_param(
        "ss",
        $busqueda,
        $busqueda
    );

}


$stmt->execute();

$resultado_horas = $stmt->get_result();

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
        Horas Beca - Sistemas Becados UNICAH
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
           BUSCADOR
        ================================================== */

        .barra-superior {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .buscador {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 600px;
        }


        .buscador input {
            flex: 1;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }


        .boton-buscar {
            background-color: #374151;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }


        .boton-buscar:hover {
            background-color: #1f2937;
        }


        .boton-limpiar {
            background-color: #6b7280;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
        }


        .boton-limpiar:hover {
            background-color: #4b5563;
        }


        /* ==================================================
           TARJETA
        ================================================== */

        .tarjeta {
            background-color: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .tarjeta h2 {
            color: #1f2937;
            margin-bottom: 20px;
        }


        /* ==================================================
           TABLA
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
            border-bottom: 1px solid #e5e7eb;
        }


        tr:hover {
            background-color: #f9fafb;
        }


        /* ==================================================
           HORAS
        ================================================== */

        .horas-realizadas {
            color: #166534;
            font-weight: bold;
        }


        .horas-pendientes {
            color: #b91c1c;
            font-weight: bold;
        }


        .barra {
            width: 180px;
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }


        .barra-progreso {
            height: 100%;
            background-color: #166534;
        }


        .porcentaje {
            margin-top: 5px;
            font-size: 13px;
            color: #6b7280;
        }


        /* ==================================================
           BOTON REGISTRAR
        ================================================== */

        .boton {
            display: inline-block;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 6px;
            color: white;
        }


        .boton-verde {
            background-color: #198754;
        }


        .boton-verde:hover {
            background-color: #157347;
        }


        .sin-registros {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 900px) {

            .menu {
                width: 200px;
            }

            .barra-superior {
                flex-direction: column;
                align-items: stretch;
            }

            .buscador {
                max-width: none;
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


        <a href="../becados/becados.php">
            Becados
        </a>


        <a href="horas.php" class="activo">
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
                Horas Beca
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
             BUSCADOR
        ================================================== -->

        <div class="barra-superior">

            <form
                method="GET"
                action="horas.php"
                class="buscador"
            >

                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar por nombre o número de cuenta..."
                    value="<?= htmlspecialchars($buscar) ?>"
                >


                <button
                    type="submit"
                    class="boton-buscar"
                >
                    Buscar
                </button>


                <?php if ($buscar !== ''): ?>

                    <a
                        href="horas.php"
                        class="boton-limpiar"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- ==================================================
             TABLA
        ================================================== -->

        <div class="tarjeta">

            <div class="tabla-contenedor">

                <table>

                    <thead>

                        <tr>

                            <th>
                                N.º Cuenta
                            </th>

                            <th>
                                Nombre completo
                            </th>

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

                            <th>
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($resultado_horas->num_rows > 0): ?>


                        <?php while ($becado = $resultado_horas->fetch_assoc()): ?>


                            <?php

                            $requeridas =
                                (int) $becado['horas_requeridas'];

                            $realizadas =
                                (int) $becado['horas_realizadas'];

                            $pendientes =
                                $requeridas - $realizadas;


                            if ($pendientes < 0) {
                                $pendientes = 0;
                            }


                            if ($requeridas > 0) {

                                $porcentaje =
                                    ($realizadas / $requeridas) * 100;

                            } else {

                                $porcentaje = 0;

                            }


                            if ($porcentaje > 100) {
                                $porcentaje = 100;
                            }

                            ?>


                            <tr>


                                <td>

                                    <?= htmlspecialchars(
                                        $becado['numero_cuenta']
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $becado['nombre_completo']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $becado['nombre_beca']
                                    ) ?>

                                </td>


                                <td>

                                    <?= number_format(
                                        $requeridas,
                                        0
                                    ) ?>

                                    horas

                                </td>


                                <td
                                    class="horas-realizadas"
                                >

                                    <?= number_format(
                                        $realizadas,
                                        0
                                    ) ?>

                                    horas

                                </td>


                                <td
                                    class="horas-pendientes"
                                >

                                    <?= number_format(
                                        $pendientes,
                                        0
                                    ) ?>

                                    horas

                                </td>


                                <td>

                                    <div class="barra">

                                        <div
                                            class="barra-progreso"
                                            style="
                                                width:
                                                <?= $porcentaje ?>%
                                            "
                                        ></div>

                                    </div>


                                    <div class="porcentaje">

                                        <?= number_format(
                                            $porcentaje,
                                            1
                                        ) ?>%

                                    </div>

                                </td>


                                <td>

                                    <a
                                        href="registrar_horas.php?id=<?= $becado['id_beca'] ?>"
                                        class="boton boton-verde"
                                    >
                                        Registrar horas
                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="sin-registros"
                            >

                                <?php if ($buscar !== ''): ?>

                                    No se encontraron becados
                                    con esa búsqueda.

                                <?php else: ?>

                                    No hay becados con becas
                                    que requieran horas.

                                <?php endif; ?>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </main>

</div>


</body>

</html>