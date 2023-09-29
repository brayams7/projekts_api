<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVITE TO WORKSPACE</title>

    <style type="text/css">

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

        .content p{
            margin: 12px;
            font-size: 20px;
        }

        .content p span{
            font-weight: bold;
        }

        .content a{
            display: block;
            padding: 10px 16px;
            background-color: #44546f;
            color: white;
            border-radius: 8px;
            margin-bottom: 12px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PROJEKTS</h1>
        <div class="content">

            <p>¿Unirte al grupo de trabajo <span>{{$nameWorkspace}}</span>? </p>

            <a href="{{$url}}" target="_blank">Aceptar invitación</a>
        </div>
    </div>
</body>
</html>
