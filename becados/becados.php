<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


// ===============================
// BUSCADOR
// ===============================

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';


// ===============================
// CONSULTA DE BECADOS
// ===============================

$sql = "SELECT 
            b.id_becado,
            b.numero_cuenta,
            b.nombre_completo,
            c.nombre_carrera,
            e.nombre_estado,
            be.id_beca,
            tb.nombre_beca

        FROM becados b

        LEFT JOIN carreras c
            ON b.id_carrera = c.id_carrera

        LEFT JOIN estados e
            ON b.id_estado = e.id_estado

        LEFT JOIN becas be
            ON be.id_beca = (
                SELECT MAX(b2.id_beca)
                FROM becas b2
                WHERE b2.id_becado = b.id_becado
            )

        LEFT JOIN tipos_beca tb
            ON be.id_tipo_beca = tb.id_tipo_beca";


// ===============================
// FILTRO DE BÚSQUEDA
// ===============================

if ($buscar != '') {

    $buscar_seguro = $conexion->real_escape_string($buscar);

    $sql .= " WHERE 
                b.nombre_completo LIKE '%$buscar_seguro%'
                OR b.numero_cuenta LIKE '%$buscar_seguro%'";

}


$sql .= " ORDER BY b.nombre_completo ASC";


$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Becados - Sistemas Becados UNICAH</title>

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

        /* =========================
           MENU
        ========================= */

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


        /* =========================
           CONTENIDO
        ========================= */

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


        /* =========================
           BARRA DE ACCIONES
        ========================= */

        .barra {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .busqueda {
            display: flex;
            gap: 10px;
        }

        .busqueda input {
            width: 350px;
            padding: 11px;

            border: 1px solid #d1d5db;
            border-radius: 6px;

            outline: none;
        }

        .busqueda input:focus {
            border-color: #2563eb;
        }

        button {
            border: none;
            padding: 11px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .buscar {
            background-color: #374151;
            color: white;
        }

        .buscar:hover {
            background-color: #1f2937;
        }

        .nuevo {
            background-color: #166534;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .nuevo:hover {
            background-color: #14532d;
        }


        /* =========================
           TABLA
        ========================= */

        .tabla-contenedor {
            background-color: white;
            border-radius: 10px;
            overflow-x: auto;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            
        }
            .col-acciones {
                text-align: center;
                width: 180px;
            }

            .acciones {
                text-align: center;
                white-space: nowrap;
            }

            .acciones .btn-ver,
            .acciones .btn-editar {
                display: inline-block;
                margin: 2px;
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


        /* =========================
           BOTON VER
        ========================= */

        .ver {
            background-color: #2563eb;
            color: white;
            text-decoration: none;

            padding: 7px 12px;
            border-radius: 5px;
        }
        .editar {
    background-color: #d97706;
    color: white;
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 5px;
    margin-left: 5px;
}

.editar:hover {
    background-color: #b45309;
}

        .ver:hover {
            background-color: #1d4ed8;
        }


        /* =========================
           SIN REGISTROS
        ========================= */

        .sin-registros {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }

    </style>

</head>

<body>

<div class="contenedor">


    <!-- =========================
         MENU LATERAL
    ========================= -->

    <aside class="menu">

        <h2>Sistemas Becados</h2>


        <a href="../dashboard/dashboard.php">
            Inicio
        </a>


        <a href="becados.php" class="activo">
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


        <a href="../login/logout.php" class="cerrar">
            Cerrar sesión
        </a>

    </aside>



    <!-- =========================
         CONTENIDO
    ========================= -->

    <main class="contenido">


        <!-- ENCABEZADO -->

        <div class="encabezado">

            <h1>Becados</h1>


            <div class="usuario">

                Bienvenido,

                <strong>
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </strong>

            </div>

        </div>



        <!-- =========================
             BARRA DE ACCIONES
        ========================= -->

        <div class="barra">


            <!-- BUSCADOR -->

            <form class="busqueda" method="GET">

                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar por nombre o número de cuenta..."
                    value="<?php echo htmlspecialchars($buscar); ?>"
                >


                <button
                    type="submit"
                    class="buscar"
                >
                    Buscar
                </button>

            </form>



            <!-- NUEVO BECADO -->

            <a
                href="nuevo_becado.php"
                class="nuevo"
            >
                + Nuevo becado
            </a>

        </div>



        <!-- =========================
             TABLA
        ========================= -->

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>

                        <th>N.º Cuenta</th>

                        <th>Nombre completo</th>

                        <th>Carrera</th>

                        <th>Tipo de beca</th>

                        <th>Estado</th>

                        <th>Acción</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($resultado && $resultado->num_rows > 0) {

                    while ($becado = $resultado->fetch_assoc()) {

                ?>


                    <tr>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $becado['numero_cuenta']
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $becado['nombre_completo']
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $becado['nombre_carrera'] ?? 'Sin carrera'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $becado['nombre_beca'] ?? 'Sin beca'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $becado['nombre_estado'] ?? 'Sin estado'
                            );
                            ?>
                        </td>


                        <td>

                            <td>

                            <a 
                                class="ver"
                                href="ver_becado.php?id=<?php echo $becado['id_becado']; ?>"
                            >
                                Ver
                            </a>

                            <a 
                                class="editar"
                                href="editar_becado.php?id=<?php echo $becado['id_becado']; ?>"
                            >
                                Editar
                            </a>

                        </td>

                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="6"
                            class="sin-registros"
                        >

                            <?php

                            if ($buscar != '') {

                                echo "No se encontraron becados para: "
                                    . htmlspecialchars($buscar);

                            } else {

                                echo "No hay becados registrados actualmente.";

                            }

                            ?>

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>


    </main>

</div>

</body>

</html>