<?php

$host = "127.0.0.1";
$port = 3306;
$database = "gestor_conciertos";
$username = "oscar";
$password = "561Cazadora#%";

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);