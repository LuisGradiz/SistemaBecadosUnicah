<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


// ==========================================
// CARGAR CARRERAS
// ==========================================

$sql_carreras = "SELECT id_carrera, nombre_carrera
                 FROM carreras
                 WHERE estado = '1'
                 ORDER BY nombre_carrera ASC";

$carreras = $conexion->query($sql_carreras);


// ==========================================
// CARGAR ESTADOS
// ==========================================

$sql_estados = "SELECT id_estado, nombre_estado
                FROM estados
                ORDER BY nombre_estado ASC";

$estados = $conexion->query($sql_estados);


// ==========================================
// CARGAR TIPOS DE BECA
// ==========================================

$sql_becas = "SELECT
                id_tipo_beca,
                nombre_beca,
                requiere_horas,
                horas_requeridas
              FROM tipos_beca
              WHERE estado = '1'
              ORDER BY nombre_beca ASC";

$tipos_beca = $conexion->query($sql_becas);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nuevo Becado - Sistemas Becados UNICAH</title>

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
        }


        /* =========================
           FORMULARIO
        ========================= */

        .formulario {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .formulario h2 {
            margin-bottom: 25px;
            color: #1f2937;
        }

        .grupo {
            margin-bottom: 20px;
        }

        .grupo label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #374151;
        }

        .grupo input,
        .grupo select,
        .grupo textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .grupo textarea {
            resize: vertical;
            min-height: 100px;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }


        /* =========================
           HORAS
        ========================= */

        .info-horas {
            background-color: #f3f4f6;
            padding: 12px 15px;
            border-radius: 6px;
            margin-top: 8px;
            color: #374151;
        }


        /* =========================
           BOTONES
        ========================= */

        .botones {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .guardar {
            background-color: #166534;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .cancelar {
            background-color: #6b7280;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
        }

        .guardar:hover {
            background-color: #14532d;
        }

        .cancelar:hover {
            background-color: #4b5563;
        }

    </style>

</head>


<body>

<div class="contenedor">


    <!-- =========================
         MENU
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


        <div class="encabezado">

            <h1>Nuevo Becado</h1>

            <div class="usuario">

                Bienvenido,

                <strong>
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </strong>

            </div>

        </div>


        <!-- =========================
             FORMULARIO
        ========================= -->

        <div class="formulario">

            <h2>Información del becado</h2>


            <form action="guardar_becado.php" method="POST">


                <!-- CUENTA -->

                <div class="grupo">

                    <label for="numero_cuenta">
                        Número de cuenta
                    </label>

                    <input
                        type="text"
                        id="numero_cuenta"
                        name="numero_cuenta"
                        required
                    >

                </div>


                <!-- NOMBRE -->

                <div class="grupo">

                    <label for="nombre_completo">
                        Nombre completo
                    </label>

                    <input
                        type="text"
                        id="nombre_completo"
                        name="nombre_completo"
                        required
                    >

                </div>


                <!-- CARRERA Y ESTADO -->

                <div class="fila">


                    <div class="grupo">

                        <label for="id_carrera">
                            Carrera
                        </label>

                        <select
                            id="id_carrera"
                            name="id_carrera"
                            required
                        >

                            <option value="">
                                Seleccione una carrera
                            </option>

                            <?php

                            if ($carreras && $carreras->num_rows > 0) {

                                while ($carrera = $carreras->fetch_assoc()) {

                            ?>

                                <option
                                    value="<?php echo $carrera['id_carrera']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $carrera['nombre_carrera']
                                    );
                                    ?>

                                </option>

                            <?php

                                }

                            }

                            ?>

                        </select>

                    </div>



                    <div class="grupo">

                        <label for="id_estado">
                            Estado
                        </label>

                        <select
                            id="id_estado"
                            name="id_estado"
                            required
                        >

                            <option value="">
                                Seleccione un estado
                            </option>

                            <?php

                            if ($estados && $estados->num_rows > 0) {

                                while ($estado = $estados->fetch_assoc()) {

                            ?>

                                <option
                                    value="<?php echo $estado['id_estado']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $estado['nombre_estado']
                                    );
                                    ?>

                                </option>

                            <?php

                                }

                            }

                            ?>

                        </select>

                    </div>

                </div>


                <!-- TELEFONO Y CORREO -->

                <div class="fila">


                    <div class="grupo">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                        >

                    </div>


                    <div class="grupo">

                        <label for="correo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="correo"
                            name="correo"
                        >

                    </div>

                </div>


                <!-- TIPO DE BECA -->

                <div class="grupo">

                    <label for="id_tipo_beca">
                        Tipo de beca
                    </label>

                    <select
                        id="id_tipo_beca"
                        name="id_tipo_beca"
                        required
                        onchange="mostrarHoras()"
                    >

                        <option value="">
                            Seleccione un tipo de beca
                        </option>

                        <?php

                        if ($tipos_beca && $tipos_beca->num_rows > 0) {

                            while ($beca = $tipos_beca->fetch_assoc()) {

                        ?>

                            <option
                                value="<?php echo $beca['id_tipo_beca']; ?>"
                                data-requiere="<?php echo $beca['requiere_horas']; ?>"
                                data-horas="<?php echo $beca['horas_requeridas']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $beca['nombre_beca']
                                );
                                ?>

                            </option>

                        <?php

                            }

                        }

                        ?>

                    </select>


                    <div
                        id="infoHoras"
                        class="info-horas"
                        style="display:none;"
                    >

                        Horas requeridas:
                        <strong id="cantidadHoras">0</strong>

                    </div>

                </div>


                <!-- FECHA INICIO -->

                <div class="grupo">

                    <label for="fecha_inicio">
                        Fecha de inicio de la beca
                    </label>

                    <div class="campo">

    <label>Período de inicio</label>

    <select name="numero_periodo_inicio" required>

        <option value="">Seleccione un período</option>

        <option value="1">1 período</option>

        <option value="2">2 período</option>

        <option value="3">3 período</option>

    </select>

</div>


<div class="campo">

    <label>Año de inicio</label>

    <select name="anio_inicio" required>

        <option value="">Seleccione un año</option>

        <?php

        $anio_actual = date("Y");

        for ($anio = 2000; $anio <= $anio_actual; $anio++) {

            echo '<option value="' . $anio . '">' .
                 $anio .
                 '</option>';

        }

        ?>

    </select>

</div>

                </div>


                <!-- DETALLE -->

                <div class="grupo">

                    <label for="detalle">
                        Detalle de la beca
                    </label>

                    <textarea
                        id="detalle"
                        name="detalle"
                        placeholder="Información adicional de la beca..."
                    ></textarea>

                </div>


                <!-- OBSERVACIONES -->

                <div class="grupo">

                    <label for="observaciones">
                        Observaciones
                    </label>

                    <textarea
                        id="observaciones"
                        name="observaciones"
                    ></textarea>

                </div>


                <!-- BOTONES -->

                <div class="botones">

                    <button
                        type="submit"
                        class="guardar"
                    >
                        Guardar becado
                    </button>


                    <a
                        href="becados.php"
                        class="cancelar"
                    >
                        Cancelar
                    </a>

                </div>


            </form>

        </div>

    </main>

</div>


<script>

function mostrarHoras() {

    const select = document.getElementById("id_tipo_beca");

    const opcion = select.options[select.selectedIndex];

    const requiere = opcion.getAttribute("data-requiere");

    const horas = opcion.getAttribute("data-horas");

    const info = document.getElementById("infoHoras");

    const cantidad = document.getElementById("cantidadHoras");


    if (requiere == "1") {

        info.style.display = "block";

        cantidad.textContent = horas;

    } else {

        info.style.display = "none";

        cantidad.textContent = "0";

    }

}

</script>


</body>

</html>