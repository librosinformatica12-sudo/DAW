<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>aa</h1>
</body>
    <?php
    session_start();
    $_SESSION['numero'] = 42;   // guardar
    echo $_SESSION['numero'];   // leer

    ?>
</html>
