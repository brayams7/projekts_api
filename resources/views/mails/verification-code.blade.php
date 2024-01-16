<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VERIFICACIÓN DE CORREO</title>

    <style>

        body{
            font-family: sans-serif;
        }

        h1{
            margin: 12px 0;
            font-size: 28px;
            font-weight: bold;
        }

        .container{
            background-color: #a0aec0;
            width: 100%;
            text-align: left;
        }

        .content{
            display: flex;
            flex-direction: column;
            min-height: 300px;
            align-items: center;
            justify-content: center;
            width: 90%;
            background-color: #f7fafc;
            border: 1px solid rgb(128, 128, 128);
            border-radius: 8px;
            text-align: center;
            padding: 10px;
            margin: 10px auto;
        }

        .content h2{
            font-size: 18px;
            margin: auto;
            text-align: center;
            padding-bottom: 10px;
        }

        .content p{
            margin: 12px;
            font-size: 20px;
        }

        .content h3{
            font-weight: bold;
            font-size: 40px;
            margin: auto;
            text-align: center;
        }


    </style>
</head>
<body>
    <div class="container">
        <h1>Confirmación de correo</h1>
        <div class="content">

            <h2>
                Hola {{$name}}
            </h2>

            <p>Ingresa este código en la plataforma de Projeckts para confirmar tu correo electrónico</p>

            <h3>{{$code}}</h3>
        </div>
    </div>
</body>
</html>
