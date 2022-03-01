<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
//$inputKey = $request['key'] ?? null;

//Ключ 256 бит
//$inputKey = '5052cf0a3c02bb874d3befaaec7affe7bb03ad1fd24b855de2435e8f81961415';

//$p = gmp_init('7');
$p = gmp_init('57896044618658097711785492504343953926634992332820282019728792003956564821041');
print('Простое число p='.$p."<br>");
//$q = gmp_init('13');
$q = gmp_init('3623986102229003635907788753683874306021320925534678605086546150450856166624002482588482022271496854025090823603058735163734263822371964987228582907372403');
print('Простое число q='.$q."<br>");
//$n = $p*$q
$n = gmp_mul($p,$q);
//print('$n = $p*$q'.$n."<br>");
//$eulN = ($p - 1)*($q-1);
$eulN = gmp_mul((gmp_sub($p, 1)), (gmp_sub($q, 1)));
//print('Эйлера $eulN = ($p - 1)*($q-1)='.$eulN."<br>");

$e = (function() use ($n, $p, $q, $eulN) {
    while (true) {
        $rand = gmp_random_range(gmp_init(3), $n);

//        if(($rand % $p) === 0 || ($rand % $q) === 0) {
//        if((gmp_cmp(gmp_div_r($rand, $p), 0) === 0) || (gmp_cmp(gmp_div_r($rand, $q), 0)) === 0) {
        if(gmp_cmp(gmp_gcd($rand,$eulN), 1) !== 0) {
            continue;
        }

        return $rand;
    }
})();


$euclid = new Euclidean($e, $eulN);
$d=$euclid->euclideanReverseNumber();
print('Закрытый ключ D: '.$d."<br><br>");


$m = $inputText;
$m = hash('sha256', $m);
print('Хэш открытого текста(sha256): '.$m."<br>");

$m = gmp_init($m, 16);
$cipherText = gmp_powm($m,$d,$n);
//Подпись
$cipherText = gmp_strval($cipherText,16);

print('Подпись: '.$cipherText."<br>");

//Расшифровка
$cipherText = gmp_init($cipherText, 16);
$m = $inputText;
$m = hash('sha256', $m);

$sendedM = gmp_powm($cipherText,$e,$n);
print('Хеш от расшифровки: '.gmp_strval($sendedM, 16)."<br>");

class Euclidean
{
    public function __construct(
        protected $num,
        protected $mod
    ) {}

    public function euclideanReverseNumber()
    {
        $a = $this->mod;
        $b = $this->num;
        $remainder = $this->mod;

        $P = gmp_init(1);
        $prevprevP = gmp_init(0);
        $n = gmp_init(1);
        while (gmp_cmp($remainder, 0) !== 0) {
            [$q, $remainder] = gmp_div_qr($a, $b);
            if (gmp_cmp($remainder,0) === 0) {
                $n = gmp_sub($n, 1);
                break;
            }
            $a = $b;
            $b = $remainder;

            $prevP = $P;
//            $P = $q*$prevP+$prevprevP;
            $P = gmp_add(gmp_mul($q,$prevP), $prevprevP);
            $prevprevP = $prevP;

            $n = gmp_add($n, 1);
        }

//        $x = mod(pow(-1, $n-1)*$P*1, $this->mod);
        $x = gmp_mod(gmp_mul(gmp_cmp(gmp_mod($n, 2), 0) === 0? 1 : -1, $P), $this->mod);

        return $x;
    }

    protected function euclideanIterDiv($mod, $num)
    {
        $q = floor($mod / $num);
        $remainder = $mod % $num;

        return [$q, $remainder];
    }
}