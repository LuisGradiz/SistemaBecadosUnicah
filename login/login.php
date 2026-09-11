<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio de sesión - Sistemas Becados UNICAH</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background: #f3f5f8;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        .contenedor-login {
            width: 100%;
            max-width: 430px;

            background: white;

            padding: 40px;

            border-radius: 15px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .encabezado {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;

            margin: 0 auto 20px;

            background: #1f2937;

            border-radius: 50%;

            display: flex;
            justify-content: center;
            align-items: center;

            color: white;

            font-size: 28px;
            font-weight: bold;
        }

        h1 {
            color: #1f2937;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .subtitulo {
            color: #6b7280;
            font-size: 15px;
        }

        .campo {
            margin-bottom: 20px;
        }

        .campo label {
            display: block;

            margin-bottom: 8px;

            color: #374151;

            font-weight: bold;

            font-size: 14px;
        }

        .campo input {
            width: 100%;

            padding: 13px 14px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            outline: none;

            transition: 0.2s;
        }

        .campo input:focus {
            border-color: #1f2937;

            box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
        }

        .boton {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #1f2937;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .boton:hover {
            background: #111827;
        }

        .pie {
            text-align: center;

            margin-top: 25px;

            color: #9ca3af;

            font-size: 13px;
        }

    </style>

</head>

<body>

    <div class="contenedor-login">

        <div class="encabezado">

            <div class="logo">
                SB
            </div>

            <h1>Sistemas Becados UNICAH</h1>

            <p class="subtitulo">
                Inicio de sesión
            </p>

        </div>


        <form action="/SistemaBecadosUNICAH/login/validar_login.php" method="POST">

            <div class="campo">

                <label for="usuario">
                    Usuario
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Ingrese su usuario"
                    required
                    autocomplete="username"
                >

            </div>


            <div class="campo">

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                    required
                    autocomplete="current-password"
                >

            </div>


            <button type="submit" class="boton">
                Iniciar sesión
            </button>

        </form>


        <div class="pie">

            Sistema de Becados<br>
            UNICAH

        </div>

    </div>

</body>

</html>