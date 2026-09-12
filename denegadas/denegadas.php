<?php

require_once "../login/validar_sesion.php";
require_once "../config/conexion.php";

$buscar = trim($_GET['buscar'] ?? '');

$sql = "
    SELECT
        d.id_denegada,
        d.numero_cuenta,
        d.nombre_completo,
        d.fecha,
        d.detalle,
        d.observaciones,
        tb.nombre_beca
    FROM denegadas d
    INNER JOIN tipos_beca tb
        ON d.id_tipo_beca = tb.id_tipo_beca
";

if ($buscar !== '') {

    $sql .= "
        WHERE
            d.numero_cuenta LIKE ?
            OR d.nombre_completo LIKE ?
            OR tb.nombre_beca LIKE ?
    ";

}

$sql .= "
    ORDER BY d.fecha DESC, d.nombre_completo ASC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta: " . $conexion->error);
}

if ($buscar !== '') {

    $parametro = "%" . $buscar . "%";

    $stmt->bind_param(
        "sss",
        $parametro,
        $parametro,
        $parametro
    );

}

$stmt->execute();

$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Denegadas - Sistema Becados</title>

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

        .contenido {
            flex: 1;
            padding: 35px;
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .encabezado h1 {
            color: #1f2937;
            font-size: 28px;
        }

        .boton {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
            font-size: 14px;
        }

        .boton:hover {
            background: #1d4ed8;
        }

        .busqueda {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .busqueda form {
            display: flex;
            gap: 10px;
        }

        .busqueda input {
            flex: 1;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .busqueda button {
            border: none;
            background: #374151;
            color: white;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .busqueda button:hover {
            background: #1f2937;
        }

        .tabla-contenedor {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1f2937;
            color: white;
            padding: 14px 12px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 13px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
            vertical-align: top;
        }

        tr:hover {
            background: #f9fafb;
        }

        .detalle {
            max-width: 250px;
        }

        .sin-datos {
            text-align: center;
            padding: 35px;
            color: #6b7280;
        }

    </style>

</head>

<body>

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


<main class="contenido">

    <div class="encabezado">

        <h1>Becas Denegadas</h1>

        <a href="nueva_denegada.php" class="boton">
            + Registrar denegada
        </a>

    </div>


    <div class="busqueda">

        <form method="GET">

            <input
                type="text"
                name="buscar"
                placeholder="Buscar por cuenta, nombre o beca..."
                value="<?= htmlspecialchars($buscar) ?>"
            >

            <button type="submit">
                Buscar
            </button>

        </form>

    </div>


    <div class="tabla-contenedor">

        <table>

            <thead>

                <tr>
                    <th>Cuenta</th>
                    <th>Nombre completo</th>
                    <th>Beca solicitada</th>
                    <th>Fecha</th>
                    <th>Detalle</th>
                    <th>Observaciones</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($resultado->num_rows > 0): ?>

                <?php while ($denegada = $resultado->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($denegada['numero_cuenta']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($denegada['nombre_completo']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($denegada['nombre_beca']) ?>
                        </td>

                        <td>
                            <?= date(
                                "d/m/Y",
                                strtotime($denegada['fecha'])
                            ) ?>
                        </td>

                        <td class="detalle">

                            <?= !empty($denegada['detalle'])
                                ? htmlspecialchars($denegada['detalle'])
                                : "—" ?>

                        </td>

                        <td class="detalle">

                            <?= !empty($denegada['observaciones'])
                                ? htmlspecialchars($denegada['observaciones'])
                                : "—" ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6" class="sin-datos">
                        No hay denegadas registradas.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

</body>

</html>