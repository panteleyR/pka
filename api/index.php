<?php

require __DIR__ . '/vendor/autoload.php';

$mod = 7;
$a = 1;
$b = 3;

$ec = new MOK\Model\EllipticCurve($mod, $a, $b);

$points = $ec->getPoints();
//print_r($ec->getPoryadok());exit;

$calc = new MOK\Services\PointCalculate($ec, true);

//foreach ($points as $point) {
//    $calc->multiplyByN($point, $mod - 1);
//    printf('P=(%s, %s) ', $point->getX(), $point->getY());
//    printf('Порядок точки: %s'. PHP_EOL, $calc->countPoryadok($point));
//}
//$point= new \MOK\Model\Point(6, 6, $mod);
//$calc->multiplyByN($point, 3);

//print_r($points[1]);exit;

//$point = new \MOK\Model\Point(1, 13, $mod);
//$pointCheck = $calc->countPoryadok($point);
//$pointCheck = $calc->multiplyByN($point, 40);
//print_r($pointCheck);