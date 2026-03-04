<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mundo Viajero</title>
    <link rel="stylesheet" href="./css/styles.css">
    <style>
        /* --- LANDING OVERLAY --- */
        #landingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('demo.jpg') center/cover no-repeat;
            animation: panBackground 20s linear infinite;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #fff;
            text-align: center;
        }

        @keyframes panBackground {
            0% {
                background-position: center top;
            }

            50% {
                background-position: center bottom;
            }

            100% {
                background-position: center top;
            }
        }

        #landingOverlay img {
            max-width: 400px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            #landingOverlay img {
                max-width: 1000px;
                margin-bottom: 20px;
            }
        }


        #landingRegisterBtn {
            background-color: #00a2ff;
            color: #000;
            border: none;
            padding: 14px 28px;
            font-size: 1.2rem;
            border-radius: 6px;
            cursor: pointer;
        }

        #landingRegisterBtn:hover {
            background-color: #008cd6;
        }
    </style>
</head>

<body>
    <div class="fondo"></div>
    <div class="capanegra"></div>

    <!-- LANDING OVERLAY -->
    <div id="landingOverlay">
        <img class="logo" src="logo.png" alt="Logo">
        <a href="register.php"><button id="landingRegisterBtn">Registrarse</button></a>
    </div>

    <!-- opcional: contenido adicional -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>