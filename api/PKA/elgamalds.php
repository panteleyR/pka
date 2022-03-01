<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
//$inputKey = $request['key'] ?? null;

//Ключ 256 бит
//$inputKey = '5052cf0a3c02bb874d3befaaec7affe7bb03ad1fd24b855de2435e8f81961415';

//$p = gmp_init('7');
$p = gmp_init('3623986102229003635907788753683874306021320925534678605086546150450856166624002482588482022271496854025090823603058735163734263822371964987228582907372403');
print('Простое число p='.$p."<br>");

$g = gmp_init('57896044618658097711785492504343953926634992332820282019728792003956564821041');
print('Простое число g='.$g."<br><br>");

//сектретный ключ
$x = gmp_random_range(gmp_init(1), gmp_sub($p,1));

//открытый ключ
$y = gmp_powm($g, $x, $p);

$m = $inputText;
$m = hash('sha256', $m);

$k = (function() use ($p) {
    while (true) {
        $rand = gmp_random_range(gmp_init(1), gmp_sub($p,1));

        if(gmp_cmp(gmp_gcd($rand, gmp_sub($p,1)), 1) !== 0) {
            continue;
        }

        return $rand;
    }
})();

//Подписание
$a = gmp_powm($g, $k, $p);
$b = gmp_sub(gmp_init($m, 16), gmp_mul($x,$a));
$euclid = new Euclidean($k, gmp_sub($p,1));
$kReverse=$euclid->euclideanReverseNumber();
$b = gmp_mod(gmp_mul($b, $kReverse), gmp_sub($p,1));

//S = ($a, $b)
//$y -- открытый ключ

//Проверка
$m = $inputText;
$m = hash('sha256', $m);
$m = gmp_init($m, 16);

$a1 = gmp_mod(gmp_mul(gmp_powm($y, $a, $p), gmp_powm($a, $b, $p)), $p);
print('Проверка <br>');
print('A1='.$a1."<br>");
$a2 = gmp_powm($g,$m,$p);
print('A2='.$a2."<br>");

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