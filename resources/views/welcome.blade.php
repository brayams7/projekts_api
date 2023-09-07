<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Syne+Mono&display=swap" rel="stylesheet">
        <title>Radioactive Area</title>
        <style>
            .container {
                height:100vh;
                width:100%;
                font-family: 'Syne Mono', monospace;
                text-align:center;
                position:relative;
            }
            .container .center {
                height:80vh;
                position: absolute;
                top:50%;
                left:50%;
                transform: translate(-50%, -50%);
            }

            .container .center h1 {
                font-size:3rem;
            }

            .container .center h3 {
                font-size:1.5rem;
            }

            .container .center h3 strong {
                font-weight:800;
            }

            .container .center img {
                max-width:70%;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="center">
                <h1>Zona radioactiva</h1>
                <img src="../css/water-polution.png" alt="Zona Radioactiva">
                <h3>Si buscas el API, no es aquí</h3>
            </div>
        </div>
        
    </body>
</html>
