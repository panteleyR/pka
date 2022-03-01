<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;
$inputText = $request['inputText'] ?? 'Тестовый текст';

//Параметры взять из госта
$a=gmp_init('7');
$b=gmp_init('43308876546767276905765904595650931995942111794451039583252968842033849580414');
$mod = gmp_init('57896044618658097711785492504343953926634992332820282019728792003956564821041');
$pointG = new Point(gmp_init('2'), gmp_init('4018974056539037503335449422937059775635739389905545080690979365213431566280'), $mod);
$q = gmp_init('57896044618658097711785492504343953927082934583725450622380973592137631069619');
printf('p=%s <br>E(%s, %s) <br> G = (%s, %s)<br><br>', $mod, $a, $b, $pointG->getX(), $pointG->getY());


$ec = new EllipticCurve($mod, $a, $b);
$calc = new PointCalculate($ec, false);

$m = $inputText;
$h = hash('sha256', $m);
$h = gmp_init($h, 16);

$r = 0;
$s = 0;
while(gmp_strval($r) == 0 || gmp_strval($s) == 0) {
    $Xu = gmp_random_range(gmp_init(1), gmp_sub($q,1));
    $k = gmp_random_range(gmp_init(1), gmp_sub($q,1));
    $Yu = $calc->multiplyByN($pointG, $Xu);
    $P = $calc->multiplyByN($pointG, $k);
    $r = gmp_mod($P->getX(), $q);
    $s = gmp_mod(gmp_add(gmp_mul($k, $h), gmp_mul($r,$Xu)), $q);
}

printf('r=%s <br><br>', $r);

if (gmp_cmp($r,$q) === 1 || gmp_cmp($s, $q) === 1) {
    throw new Exception('Подпись неверна');
}

$el = new Euclidean($h, $q);
$u1 = gmp_mod(gmp_mul($s, $el->euclideanReverseNumber()), $q);
$u2 = gmp_mod(gmp_mul(gmp_init('-'.$r), $el->euclideanReverseNumber()), $q);

$P = $calc->sum($calc->multiplyByN($pointG, $u1), $calc->multiplyByN($Yu, $u2));

if ($P instanceof NullablePoint) {
    throw new Exception('Подпись неверна');
}

$r = gmp_mod($P->getX(), $q);
printf('Проверка подписи: <br> r=%s <br>', $r);


class Point
{
    public function __construct(
        protected $x,
        protected $y,
        protected $mod
    ) {}

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function getMod()
    {
        return $this->mod;
    }
}

class EllipticCurve
{
    protected array $quadraticList = [];

    public function __construct(
        protected $mod,
        protected $a,
        protected $b,
    ) {}

    public function quadraticY($x)
    {
        return gmp_mod(gmp_add(gmp_add(gmp_powm($x, 3, $this->mod), gmp_mul($this->a,$x)), $this->b), $this->mod);
    }

//    public function getY(int $x): array
//    {
//        $quadraticY = $this->quadraticY($x);
//        $y = [];
//        foreach ($this->getQuadraticList() as $key => $el) {
//            if ($el === $quadraticY) {
//                $y[] = $key;
//            }
//        }
//        return $y;
//    }

//    public function getQuadraticList(): array
//    {
//        if ($this->quadraticList === []) {
//            for ($i = 0; $i < $this->mod; $i++) {
//                $this->quadraticList[$i] = $i * $i % $this->mod;
//            }
//        }
//        return $this->quadraticList;
//    }

//    public function getPoints(): array
//    {
//        $points = [];
//        for($x = 0; $x < $this->mod; $x++) {
//            $points[$x] = $this->getY($x);
//        }
//        $points = array_filter($points);
//        $pointsResult = [];
//        foreach ($points as $x => $yList) {
//            foreach ($yList as $y) {
//                $pointsResult[] = new Point($x, $y, $this->mod);
//            }
//        }
//        return $pointsResult;
//    }

    public function getMod()
    {
        return $this->mod;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }
}

class PointCalculate
{
    public function __construct(
        protected EllipticCurve $ec,
        protected bool $outputMode = false,
    ) {}

    public function sum(Point $pointOne, Point $pointTwo): Point
    {
        if ($pointTwo instanceof NullablePoint) {
            return $pointOne;
        }

        if ($pointOne instanceof NullablePoint) {
            return $pointTwo;
        }

        if(gmp_strval($pointOne->getX()) === gmp_strval($pointTwo->getX()) && gmp_strval($pointOne->getY()) === gmp_strval($pointTwo->getY())) {
            return $this->sumSame($pointOne);
        }

        if(gmp_strval($pointOne->getX()) === gmp_strval($pointTwo->getX())) {
            return new NullablePoint();
        }

        $L = $this->countLambda($pointOne, $pointTwo);
        $x3 = gmp_mod(gmp_sub(gmp_sub(gmp_powm($L, 2, $this->ec->getMod()), $pointOne->getX()), $pointTwo->getX()), $this->ec->getMod());
        $this->outputMode && printf('x3 = %s*%s - %s - %s = %s' . PHP_EOL, $L, $L, $pointOne->getX(), $pointTwo->getX(), $x3);
        $y3 = gmp_mod(gmp_sub(gmp_mul(gmp_sub($pointOne->getX(),$x3),$L), $pointOne->getY()), $this->ec->getMod());
        $this->outputMode && printf('y3 = %s*(%s - %s) - %s = %s' . PHP_EOL, $L, $pointOne->getX(), $x3, $pointOne->getY(), $y3);
        return new Point($x3, $y3, $this->ec->getMod());
    }

    public function multiplyByN(Point $point, $n): Point
    {
        $pointSum = $point;
        $iter = $n;
        $test = [];
        $check= false;

        if(gmp_strval(gmp_mod($iter, 2)) !== '0' && gmp_strval($iter) !== '1') {
            $check = true;
            $test[gmp_strval($iter)] = 1;
            $iter=gmp_sub($iter, 1);
        }

        while (gmp_strval($iter) !== '1') {
            if (gmp_strval(gmp_mod($iter, 2)) === '0') {
                $iter = gmp_div($iter, 2);
                $test[gmp_strval($iter)] = 2;
            } else {
                $iter = gmp_sub($iter, 1);
                $test[gmp_strval($iter)] = 1;
            }
        }

        if ($check) {
            $test[gmp_strval($iter)] = 1;
        }

        $test = array_reverse($test);
        foreach ($test as $item) {
            if ($item === 1) {
                $pointSum = $this->sum($pointSum, $point);
            } else {
                $pointSum = $this->sum($pointSum, $pointSum);
            }
        }

        return $pointSum;
    }

    public function poryadokPointG(Point $g)
    {
        $point = null;
        $k=gmp_init(1);
        while (!$point instanceof NullablePoint) {
            $point = $this->sum($g,$g);
            $k = gmp_add($k, 1);
        }
        return $k;
    }

    protected function countLambda(Point $pointOne, Point $pointTwo)
    {
//        $up = mod($pointTwo->getY() - $pointOne->getY(), $this->ec->getMod());
        $up = gmp_mod(gmp_sub($pointTwo->getY(), $pointOne->getY()), $this->ec->getMod());
//        $down = mod($pointTwo->getX() - $pointOne->getX(), $this->ec->getMod());
        $down = gmp_mod(gmp_sub($pointTwo->getX(), $pointOne->getX()), $this->ec->getMod());
        $this->outputMode && printf('L = %s-%s / %s-%s = %s/%s = %s' . PHP_EOL, $pointTwo->getY(),$pointOne->getY(),$pointTwo->getX(),$pointOne->getX(), $up, $down, $this->findDiv($up, $down));
        return $this->findDiv($up, $down);
    }

    protected function sumSame(Point $point): Point
    {
        if (gmp_strval($point->getY()) == 0) {
            return new NullablePoint();
        }

        $L = $this->countSameLambda($point);
//        $x3 = mod($L*$L - 2*$point->getX(), $this->ec->getMod());
        $x3 = gmp_mod(gmp_sub(gmp_powm($L,2,$this->ec->getMod()), gmp_mul(2,$point->getX())), $this->ec->getMod());
        $this->outputMode && printf('x3 = %s*%s - 2*%s = %s' . PHP_EOL, $L, $L, $point->getX(), $x3);
//        $y3 = mod($L*($point->getX() - $x3) - $point->getY(), $this->ec->getMod());
        $y3 = gmp_mod(gmp_sub(gmp_mul($L,(gmp_sub($point->getX(), $x3))), $point->getY()), $this->ec->getMod());
        $this->outputMode && printf('y3 = %s*(%s - %s) - %s = %s' . PHP_EOL, $L, $point->getX(), $x3, $point->getY(), $y3);
        return new Point($x3, $y3, $this->ec->getMod());
    }

    protected function countSameLambda(Point $point)
    {
        $up = gmp_mod(gmp_add(gmp_mul(3,gmp_powm($point->getX(),2, $this->ec->getMod())), $this->ec->getA()), $this->ec->getMod());
        $down = gmp_mod(gmp_mul(2,$point->getY()), $this->ec->getMod());
        $this->outputMode && printf('L = 3*%s*%s + %s / 2*%s = %s/%s = %s' . PHP_EOL, $point->getX(),$point->getX(),$this->ec->getA(),$point->getY(), $up, $down, $this->findDiv($up, $down));

        return $this->findDiv($up, $down);
    }

    public function findDiv($first, $div)
    {
        $mod = $this->ec->getMod();
        $euclid = new Euclidean($div, $mod);
        return $euclid->euclideanReverseNumber($first);
    }
}

class NullablePoint extends Point
{
    public function __construct()
    {
        $this->x = 0;
        $this->y = 0;
        $this->mod = 0;
    }
}

class Euclidean
{
    public function __construct(
        protected $num,
        protected $mod
    ) {}

    public function euclideanReverseNumber($num = 1)
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
        $x = gmp_mod(gmp_mul(gmp_mul(gmp_cmp(gmp_mod($n, 2), 0) === 0? 1 : -1, $P), $num), $this->mod);

        return $x;
    }

    protected function euclideanIterDiv($mod, $num)
    {
        $q = floor($mod / $num);
        $remainder = $mod % $num;

        return [$q, $remainder];
    }
}


class TextHexBinConverter
{
    public static int $byteSizeChar = 16;

    public static function addingBitsToBlockSize(string $text, int $sizeBlock): string
    {
        $inputTextList = mb_str_split($text);
        $needAddBits = ($sizeBlock/static::$byteSizeChar - (count($inputTextList) % ($sizeBlock/static::$byteSizeChar))) % ($sizeBlock/static::$byteSizeChar);
        if($needAddBits !== 0) {
            for($i=0;$i<$needAddBits;$i++) {
                $inputTextList[] = ' ';
            }
        }

        return implode('', $inputTextList);
    }

    public static function convertTextToHex(string $text): string
    {
        $inputTextList = mb_str_split($text);

        $inputTextHex = '';
        foreach ($inputTextList as $letter) {
            $unicodePointLetter = mb_ord($letter);
            $hexLetter = self::dechexCustom($unicodePointLetter);
            $inputTextHex .= $hexLetter;
        }

        return $inputTextHex;
    }

    public static function getHexStartZero(string $hex): string
    {
        $res = '';
        for($i=0;$i<strlen($hex); $i++){
            if($hex[$i] !== '0') {
                break;
            }

            $res .= $hex[$i];
        }
        return $res;
    }

    public static function convertTextToDec(string $text): string
    {
        $inputTextList = mb_str_split($text);

        $inputTextHex = '';
        foreach ($inputTextList as $letter) {
            $unicodePointLetter = mb_ord($letter);
            $inputTextHex .= $unicodePointLetter;
        }

        return $inputTextHex;
    }

    public static function dechexCustom(int $unicodePoint, int $bit = 16): string
    {
        $hexLetter = dechex($unicodePoint);
        $t = ($bit - self::countBitsInHexText($hexLetter))/4;
        for($i =0; $i<$t;$i++) {
            $hexLetter = '0'.$hexLetter;
        }

        return $hexLetter;
    }

    public static function convertHexToText(string $text): string
    {
        $outputTextHexList = mb_str_split($text, 4);
        $outputText = '';
        foreach ($outputTextHexList as $key => $hex) {
            $dec = hexdec($hex);
//            print_r($key." Unicode: ".$dec."<br>");
            $outputText .= mb_chr($dec);
        }

        return $outputText;
    }

    public static function convertHexToBlockList(string $hexText, int $blockSize): array
    {
        $countTextBits = self::countBitsInHexText($hexText);
        $countBlocks = $countTextBits / $blockSize;
        $countCharInBlock = strlen($hexText) / $countBlocks;

        return str_split($hexText, $countCharInBlock);
    }

    public static function countBitsInHexText(string $hexText): int
    {
        return strlen($hexText) * 4;
    }
}