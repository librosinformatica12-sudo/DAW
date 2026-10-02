<?php
$a = 10;
$b = 4;

function sumar($x, $y) {
    $resultado = $x + $y;
    return $resultado;
}

$total = sumar($a, $b);
echo "El total es: " . $total;