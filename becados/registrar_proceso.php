<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";


// ======================================================
// VALIDAR BECADO
// ======================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: becados.php");
    exit();

}

$id_becado = intval($_GET['id']);


// ======================================================
// BUSCAR BECADO
// ======================================================

$sql = "SELECT
            id_becado,
            numero_cuenta,
            nombre_completo

        FROM becados

        WHERE id_becado = ?";


$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $id_becado);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    echo "Becado no encontrado.";
    exit();

}


$becado = $resultado->fetch_assoc();

$stmt->close();

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
        Registrar proceso - Sistemas Becados UNICAH
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
           TARJETA
        ================================================== */

        .tarjeta {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            max-width: 850px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .tarjeta h2 {
            color: #1f2937;

            margin-bottom: 10px;
        }


        .subtitulo {
            color: #6b7280;

            margin-bottom: 25px;
        }


        /* ==================================================
           INFORMACIÓN DEL BECADO
        ================================================== */

        .informacion-becado {
            background-color: #f9fafb;

            border: 1px solid #e5e7eb;

            padding: 15px;

            border-radius: 7px;

            margin-bottom: 25px;
        }


        .informacion-becado strong {
            color: #1f2937;
        }


        /* ==================================================
           FORMULARIO
        ================================================== */

        .campo {
            margin-bottom: 20px;
        }


        .campo label {
            display: block;

            color: #374151;

            font-weight: bold;

            margin-bottom: 7px;
        }


        .campo select,
        .campo input,
        .campo textarea {
            width: 100%;

            padding: 11px;

            border: 1px solid #d1d5db;

            border-radius: 6px;

            font-size: 15px;
        }


        .campo textarea {
            min-height: 120px;

            resize: vertical;
        }


        .campo select:focus,
        .campo input:focus,
        .campo textarea:focus {
            outline: none;

            border-color: #2563eb;
        }


        /* ==================================================
           BOTONES
        ================================================== */

        .botones {
            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        .guardar {
            border: none;

            background-color: #166534;

            color: white;

            padding: 12px 20px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 15px;
        }


        .guardar:hover {
            background-color: #14532d;
        }


        .cancelar {
            display: inline-block;

            background-color: #6b7280;

            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 6px;
        }


        .cancelar:hover {
            background-color: #4b5563;
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


        <a href="becados.php">
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


        <div class="encabezado">

            <h1>
                Registrar proceso
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
             FORMULARIO
        ================================================== -->

        <div class="tarjeta">


            <h2>
                Nuevo proceso
            </h2>


            <p class="subtitulo">

                Registra una situación o movimiento
                importante relacionado con el becado.

            </p>


            <!-- INFORMACIÓN DEL BECADO -->

            <div class="informacion-becado">

                <strong>
                    Becado:
                </strong>

                <?php

                echo htmlspecialchars(
                    $becado['nombre_completo']
                );

                ?>

                <br>

                <strong>
                    Número de cuenta:
                </strong>

                <?php

                echo htmlspecialchars(
                    $becado['numero_cuenta']
                );

                ?>

            </div>


            <!-- FORMULARIO -->

            <form
                action="guardar_proceso.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="id_becado"
                    value="<?php echo $id_becado; ?>"
                >


                <!-- TIPO -->

                <div class="campo">

                    <label>
                        Tipo de proceso
                    </label>


                    <select
                        name="tipo_proceso"
                        required
                    >

                        <option value="">
                            Seleccione un proceso
                        </option>

                        <option value="Cambio de carrera">
                            Cambio de carrera
                        </option>

                        <option value="Cambio de beca">
                            Cambio de beca
                        </option>

                        <option value="Renovación">
                            Renovación
                        </option>

                        <option value="Suspensión">
                            Suspensión
                        </option>

                        <option value="Reactivación">
                            Reactivación
                        </option>

                        <option value="Cambio de estado">
                            Cambio de estado
                        </option>

                        <option value="Llamado de atención">
                            Llamado de atención
                        </option>

                        <option value="Cumplimiento de horas">
                            Cumplimiento de horas
                        </option>

                        <option value="Documentación">
                            Documentación
                        </option>

                        <option value="Otro">
                            Otro
                        </option>

                    </select>

                </div>


                <!-- DESCRIPCIÓN -->

                <div class="campo">

                    <label>
                        Descripción
                    </label>


                    <input
                        type="text"
                        name="descripcion"
                        placeholder="Ejemplo: Cambio de Ingeniería en Sistemas a Administración"
                        required
                    >

                </div>


                <!-- OBSERVACIÓN -->

                <div class="campo">

                    <label>
                        Observación
                    </label>


                    <textarea
                        name="observacion"
                        placeholder="Escriba aquí el motivo, información adicional o comentario relacionado con el proceso..."
                    ></textarea>

                </div>


                <!-- FECHA -->

                <div class="campo">

                    <label>
                        Fecha del proceso
                    </label>


                    <input
                        type="date"
                        name="fecha_proceso"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <!-- BOTONES -->

                <div class="botones">

                    <button
                        type="submit"
                        class="guardar"
                    >
                        Guardar proceso
                    </button>


                    <a
                        href="ver_becado.php?id=<?php echo $id_becado; ?>"
                        class="cancelar"
                    >
                        Cancelar
                    </a>

                </div>


            </form>

        </div>


    </main>

</div>


</body>

</html>