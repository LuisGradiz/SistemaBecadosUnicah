<?php
require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

$sql_becas = "
    SELECT id_tipo_beca, nombre_beca
    FROM tipos_beca
    WHERE estado = 1
    ORDER BY nombre_beca ASC
";

$resultado_becas = $conexion->query($sql_becas);
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <title>Registrar Denegada - Sistema Becados</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            display: flex;
            min-height: 100vh;
        }

        /* MENÚ */

        .menu {
            width: 240px;
            background: #1f2937;
            color: white;
            padding: 25px 15px;
            min-height: 100vh;
        }

        .menu h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 21px;
        }

        .menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
        }

        .menu a:hover {
            background: #374151;
        }

        .menu a.activo {
            background: #2563eb;
        }

        .menu a.cerrar {
            margin-top: 30px;
            background: #dc2626;
        }

        .menu a.cerrar:hover {
            background: #b91c1c;
        }

        /* CONTENIDO */

        .contenido {
            flex: 1;
            padding: 35px;
        }

        .encabezado {
            margin-bottom: 25px;
        }

        .encabezado h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .encabezado p {
            color: #6b7280;
            font-size: 14px;
        }

        /* FORMULARIO */

        .formulario {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            max-width: 900px;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        .campo label {
            color: #374151;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .campo input,
        .campo select,
        .campo textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }

        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .campo textarea {
            resize: vertical;
            min-height: 100px;
        }

        .campo-completo {
            margin-bottom: 20px;
        }

        /* BOTONES */

        .acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .boton {
            padding: 11px 20px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .guardar {
            background: #2563eb;
            color: white;
        }

        .guardar:hover {
            background: #1d4ed8;
        }

        .cancelar {
            background: #6b7280;
            color: white;
        }

        .cancelar:hover {
            background: #4b5563;
        }

        @media (max-width: 800px) {

            .fila {
                grid-template-columns: 1fr;
            }

            .menu {
                width: 200px;
            }

            .contenido {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

    <!-- MENÚ -->

    <aside class="menu">

        <h2>Sistemas Becados</h2>

        <a href="../dashboard/dashboard.php">
            Inicio
        </a>

        <a href="../becados/becados.php">
            Becados
        </a>

        <a href="../horas/horas.php">
            Horas Beca
        </a>

        <a href="../denegadas/denegadas.php" class="activo">
            Denegadas
        </a>

        <a href="../documentos/documentos.php">
            Documentos
        </a>

        <a href="../reportes/reportes.php">
            Reportes
        </a>

        <a href="../login/logout.php" class="cerrar">
            Cerrar sesión
        </a>

    </aside>


    <!-- CONTENIDO -->

    <main class="contenido">

        <div class="encabezado">

            <h1>Registrar denegada</h1>

            <p>
                Registra una solicitud de beca que fue denegada.
            </p>

        </div>


        <div class="formulario">

            <form action="guardar_denegada.php" method="POST">


                <div class="fila">

                    <div class="campo">

                        <label for="numero_cuenta">
                            Número de cuenta
                        </label>

                        <input
                            type="text"
                            id="numero_cuenta"
                            name="numero_cuenta"
                            maxlength="50"
                            required
                        >

                    </div>


                    <div class="campo">

                        <label for="nombre_completo">
                            Nombre completo
                        </label>

                        <input
                            type="text"
                            id="nombre_completo"
                            name="nombre_completo"
                            maxlength="150"
                            required
                        >

                    </div>

                </div>


                <div class="fila">

                    <div class="campo">

                        <label for="id_tipo_beca">
                            Beca solicitada
                        </label>

                        <select
                            id="id_tipo_beca"
                            name="id_tipo_beca"
                            required
                        >

                            <option value="">
                                Seleccione una beca
                            </option>

                            <?php while ($beca = $resultado_becas->fetch_assoc()): ?>

                                <option value="<?= $beca['id_tipo_beca'] ?>">
                                    <?= htmlspecialchars($beca['nombre_beca']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="fecha">
                            Fecha
                        </label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>

                </div>


                <div class="campo-completo">

                    <div class="campo">

                        <label for="detalle">
                            Detalle
                        </label>

                        <textarea
                            id="detalle"
                            name="detalle"
                            placeholder="Ingrese el detalle de la solicitud..."
                        ></textarea>

                    </div>

                </div>


                <div class="campo-completo">

                    <div class="campo">

                        <label for="observaciones">
                            Observaciones
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            placeholder="Ingrese observaciones adicionales..."
                        ></textarea>

                    </div>

                </div>


                <div class="acciones">

                    <a
                        href="denegadas.php"
                        class="boton cancelar"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="boton guardar"
                    >
                        Registrar denegada
                    </button>

                </div>

            </form>

        </div>

    </main>

</body>
</html>