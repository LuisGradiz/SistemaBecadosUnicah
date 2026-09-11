<?php

require_once "../login/validar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Sistemas Becados UNICAH</title>

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

        /* MENU LATERAL */

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

        .cerrar {
            margin-top: 30px;
            background-color: #b91c1c;
        }

        .cerrar:hover {
            background-color: #991b1b !important;
        }

        /* CONTENIDO */

        .contenido {
            flex: 1;
            padding: 30px;
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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

        /* TARJETAS */

        .tarjetas {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .tarjeta {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .tarjeta h3 {
            color: #6b7280;
            margin-bottom: 15px;
        }

        .numero {
            font-size: 32px;
            font-weight: bold;
            color: #111827;
        }

        /* BIENVENIDA */

        .bienvenida {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .bienvenida h2 {
            margin-bottom: 15px;
            color: #1f2937;
        }

        .bienvenida p {
            color: #6b7280;
            line-height: 1.6;
        }

    </style>

</head>

<body>

<div class="contenedor">

    <!-- MENU -->

    <aside class="menu">

        <h2>Sistemas Becados</h2>

        <a href="dashboard.php">
            Inicio
        </a>

        <a href="../becados/becados.php">
            Becados
        </a>

        <a href="../horas/horas.php">
            Horas Beca
        </a>

        <a href="../denegadas/denegadas.php">
            Denegadas
        </a>

        <a href="../documentos/documentos.php">
            Documentos
        </a>

        <a href="../reportes/reportes.php">
            Reportes
        </a>

        <a class="cerrar" href="../login/logout.php">
            Cerrar sesión
        </a>

    </aside>


    <!-- CONTENIDO -->

    <main class="contenido">

        <div class="encabezado">

            <h1>Dashboard</h1>

            <div class="usuario">

                Bienvenido,
                <strong>
                    <?php echo $_SESSION['nombre']; ?>
                </strong>

            </div>

        </div>


        <!-- TARJETAS -->

        <div class="tarjetas">

            <div class="tarjeta">

                <h3>Total de becados</h3>

                <div class="numero">
                    0
                </div>

            </div>


            <div class="tarjeta">

                <h3>Beca Completa</h3>

                <div class="numero">
                    0
                </div>

            </div>


            <div class="tarjeta">

                <h3>Media Beca</h3>

                <div class="numero">
                    0
                </div>

            </div>


            <div class="tarjeta">

                <h3>Beca Diócesis</h3>

                <div class="numero">
                    0
                </div>

            </div>

        </div>


        <!-- BIENVENIDA -->

        <div class="bienvenida">

            <h2>Sistema de Becados UNICAH</h2>

            <p>
                Desde este sistema podrá administrar la información
                de los becados, consultar sus períodos de beca,
                registrar horas de servicio, gestionar documentación,
                consultar becas denegadas y generar reportes.
            </p>

        </div>

    </main>

</div>

</body>

</html>