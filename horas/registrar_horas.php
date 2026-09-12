<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

$id_beca = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_beca <= 0) {
    die("Beca no válida.");
}

/* ==============================
   DATOS DEL BECADO
   ============================== */

$sql = "SELECT 
            b.id_becado,
            b.numero_cuenta,
            b.nombre_completo,
            tb.nombre_beca,
            tb.horas_requeridas
        FROM becas be
        INNER JOIN becados b 
            ON b.id_becado = be.id_becado
        INNER JOIN tipos_beca tb 
            ON tb.id_tipo_beca = be.id_tipo_beca
        WHERE be.id_beca = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_beca);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Beca no encontrada.");
}

$becado = $resultado->fetch_assoc();

$stmt->close();


/* ==============================
   HISTORIAL DE HORAS
   ============================== */

$sql = "SELECT 
            id_hora,
            fecha,
            actividad,
            horas,
            observacion
        FROM horas_beca
        WHERE id_beca = ?
        ORDER BY fecha DESC, id_hora DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_beca);
$stmt->execute();

$historial = $stmt->get_result();


/* ==============================
   TOTAL DE HORAS
   ============================== */

$sql = "SELECT COALESCE(SUM(horas), 0) AS total_horas
        FROM horas_beca
        WHERE id_beca = ?";

$stmt_total = $conexion->prepare($sql);
$stmt_total->bind_param("i", $id_beca);
$stmt_total->execute();

$total_resultado = $stmt_total->get_result()->fetch_assoc();

$total_horas = (int) $total_resultado['total_horas'];

$horas_requeridas = (int) $becado['horas_requeridas'];

$horas_pendientes = max(
    0,
    $horas_requeridas - $total_horas
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Registrar Horas Beca</title>

   <style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f6f9;
    color: #111827;
}


/* ==========================================
   MENU LATERAL
========================================== */

.menu {
    position: fixed;
    left: 0;
    top: 0;

    width: 235px;
    height: 100vh;

    background-color: #1f2937;

    padding: 25px 15px;

    color: white;
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

    padding: 13px 15px;

    margin-bottom: 6px;

    border-radius: 6px;

    font-size: 16px;
}

.menu a:hover {
    background-color: #374151;
}

.menu a.activo {
    background-color: #374151;
}

.menu .cerrar {
    margin-top: 30px;
}

.menu .cerrar:hover {
    background-color: #b91c1c;
}


/* ==========================================
   CONTENIDO
========================================== */

.contenido {
    margin-left: 235px;

    padding: 35px;
}

.contenedor {
    max-width: 1200px;
    margin: 0 auto;
}


/* ==========================================
   VOLVER
========================================== */

.volver {
    display: inline-block;

    margin-bottom: 25px;

    color: #374151;

    text-decoration: none;

    font-size: 16px;
}

.volver:hover {
    text-decoration: underline;
}


/* ==========================================
   TARJETAS
========================================== */

.card {
    background-color: white;

    border-radius: 10px;

    padding: 30px;

    margin-bottom: 25px;

    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}


/* ==========================================
   TITULOS
========================================== */

.card h1 {
    font-size: 32px;

    color: #111827;

    margin-bottom: 25px;
}

.card h2 {
    font-size: 24px;

    color: #111827;

    margin-bottom: 20px;
}


/* ==========================================
   DATOS DEL BECADO
========================================== */

.datos {
    margin-bottom: 25px;
}

.datos p {
    margin-bottom: 8px;

    font-size: 16px;

    color: #111827;
}

.datos strong {
    font-weight: bold;
}


/* ==========================================
   RESUMEN DE HORAS
========================================== */

.resumen {
    display: flex;

    gap: 15px;

    margin-bottom: 30px;

    flex-wrap: wrap;
}

.resumen div {
    background-color: #f1f3f5;

    padding: 15px 22px;

    border-radius: 8px;

    min-width: 175px;

    color: #111827;

    font-size: 16px;
}

.resumen strong {
    display: block;

    margin-top: 6px;

    font-size: 22px;

    color: #111827;
}


/* ==========================================
   FORMULARIO
========================================== */

.formulario {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}

.campo {
    display: flex;

    flex-direction: column;
}

.campo.completo {
    grid-column: 1 / 3;
}

.campo label {
    margin-bottom: 8px;

    font-size: 16px;

    font-weight: bold;

    color: #111827;
}

.campo input,
.campo textarea {
    width: 100%;

    padding: 11px 13px;

    border: 1px solid #d1d5db;

    border-radius: 6px;

    background-color: white;

    font-size: 15px;

    outline: none;
}

.campo input:focus,
.campo textarea:focus {
    border-color: #374151;

    box-shadow: 0 0 0 2px rgba(55, 65, 81, 0.10);
}

.campo textarea {
    min-height: 90px;

    resize: vertical;
}




/* ==========================================
   BOTONES
========================================== */

.botones {
    margin-top: 25px;
}

.botones button {
    background-color: #198754;
    color: white;
    border: none;
    padding: 11px 20px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
}

.botones button:hover {
    background-color: #157347;
}


/* ==========================================
   BOTÓN VOLVER
========================================== */

.volver {
    display: inline-block;

    background-color: #374151;
    color: white;

    text-decoration: none;

    padding: 11px 16px;

    border-radius: 6px;

    font-size: 16px;

    margin-bottom: 25px;
}

.volver:hover {
    background-color: #1f2937;
}


/* ==========================================
   CERRAR SESIÓN
========================================== */

.menu .cerrar {
    margin-top: 30px;

    background-color: #c91c1c;

    color: white;

    padding: 13px 15px;

    border-radius: 6px;
}

.menu .cerrar:hover {
    background-color: #b91c1c;
}


/* ==========================================
   HISTORIAL
========================================== */

.card table {
    width: 100%;

    border-collapse: collapse;
}

.card table th {
    background-color: #1f2937;

    color: white;

    padding: 13px;

    text-align: left;

    font-size: 15px;
}

.card table td {
    padding: 12px;

    border-bottom: 1px solid #e5e7eb;

    font-size: 15px;

    color: #111827;
}

.card table tbody tr:hover {
    background-color: #f9fafb;
}

.sin-registros {
    text-align: center;

    padding: 25px;

    color: #6b7280;
}


/* ==========================================
   RESPONSIVE
========================================== */

@media (max-width: 900px) {

    .menu {
        width: 200px;
    }

    .contenido {
        margin-left: 200px;

        padding: 25px;
    }

    .formulario {
        grid-template-columns: 1fr;
    }

    .campo.completo {
        grid-column: 1;
    }

}

@media (max-width: 650px) {

    .menu {
        position: relative;

        width: 100%;

        height: auto;
    }

    .contenido {
        margin-left: 0;

        padding: 20px;
    }

    .resumen {
        flex-direction: column;
    }

    .resumen div {
        width: 100%;
    }

}

</style>

</head>

<body>


<!-- ==============================
     MENU
================================= -->

<aside class="menu">

    <h2>Sistemas Becados</h2>

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
        Documentos
    </a>

    <a href="../reportes/reportes.php">
        Reportes
    </a>

    <a class="cerrar" href="../login/logout.php">
        Cerrar sesión
    </a>

</aside>


<!-- ==============================
     CONTENIDO
================================= -->

<div class="contenido">

    <div class="contenedor">

        <a href="horas.php" class="volver">
            ← Volver a Horas Beca
        </a>


        <!-- DATOS Y REGISTRO -->

        <div class="card">

            <h1>Registrar Horas</h1>


            <div class="datos">

                <p>
                    <strong>Cuenta:</strong>
                    <?= htmlspecialchars($becado['numero_cuenta']) ?>
                </p>

                <p>
                    <strong>Becado:</strong>
                    <?= htmlspecialchars($becado['nombre_completo']) ?>
                </p>

                <p>
                    <strong>Beca:</strong>
                    <?= htmlspecialchars($becado['nombre_beca']) ?>
                </p>

            </div>


            <!-- RESUMEN -->

            <div class="resumen">

                <div>
                    Horas requeridas
                    <strong>
                        <?= number_format($horas_requeridas, 0) ?>
                    </strong>
                </div>

                <div>
                    Horas realizadas
                    <strong>
                        <?= number_format($total_horas, 0) ?>
                    </strong>
                </div>

                <div>
                    Horas pendientes
                    <strong>
                        <?= number_format($horas_pendientes, 0) ?>
                    </strong>
                </div>

            </div>


            <!-- FORMULARIO -->

            <h2>Nuevo registro</h2>

            <form action="guardar_horas.php" method="POST">

                <input
                    type="hidden"
                    name="id_beca"
                    value="<?= $id_beca ?>"
                >


                <div class="formulario">


                    <!-- DESDE -->

                    <div class="campo">

                        <label for="fecha_desde">
                            Desde
                        </label>

                        <input
                            type="date"
                            id="fecha_desde"
                            name="fecha_desde"
                            required
                        >

                    </div>


                    <!-- HASTA -->

                    <div class="campo">

                        <label for="fecha_hasta">
                            Hasta
                        </label>

                        <input
                            type="date"
                            id="fecha_hasta"
                            name="fecha_hasta"
                            required
                        >

                    </div>


                    <!-- ACTIVIDAD -->

                    <div class="campo completo">

                        <label for="actividad">
                            Actividad
                        </label>

                        <input
                            type="text"
                            id="actividad"
                            name="actividad"
                            maxlength="255"
                            required
                        >

                    </div>


                    <!-- HORAS -->

                    <div class="campo">

                        <label for="horas">
                            Horas realizadas
                        </label>

                        <input
                            type="number"
                            id="horas"
                            name="horas"
                            min="1"
                            step="1"
                            required
                        >

                    </div>


                    <!-- OBSERVACION -->

                    <div class="campo">

                        <label for="observacion">
                            Observación
                        </label>

                        <textarea
                            id="observacion"
                            name="observacion"
                            maxlength="500"
                        ></textarea>

                    </div>

                </div>


                <div class="botones">

                    <button type="submit">
                        Guardar horas
                    </button>

                </div>

            </form>

        </div>


        <!-- ==============================
             HISTORIAL
        ================================= -->

        <div class="card">

            <h2>Historial de horas</h2>


            <?php if ($historial->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>Fecha</th>

                            <th>Actividad</th>

                            <th>Horas</th>

                            <th>Observación</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while ($hora = $historial->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= date(
                                        "d/m/Y",
                                        strtotime($hora['fecha'])
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $hora['actividad']
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (int)$hora['horas'],
                                        0
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $hora['observacion'] ?? ''
                                    ) ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="sin-registros">

                    No hay horas registradas todavía.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>