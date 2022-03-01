<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;
$inputText = $request['inputText'] ?? 'Тестовый текст';

$m = $inputText;
$h = hash('sha256', $m);
$h = gmp_init($h, 16);

//$p = gmp_init('ee8172ae 854510e2 ea0a12b3 6bb0c345 8996608f 977a4d63 43e9190f d165976e b69359b8 bc97322c 23177539 f2195ec9 9eb82a69 e5dc3386 84583978 b1c379e3',16);
$p = gmp_init('ee8172ae 8996608f b69359b8 9eb82a69 854510e2 977a4d63 bc97322c e5dc3386 ea0a12b3 43e9190f 23177539 84583978 6bb0c345 d165976e f2195ec9 b1c379e3',16);
printf('p = %s<br>', $p);

//$q = gmp_init('98915e7e b064bdc7 c8265edf 285dd50d cda31e88 7289f0ac f24809dd 6f49dd2d',16);
$q = gmp_init('98915e7e c8265edf cda31e88 f24809dd b064bdc7 285dd50d 7289f0ac 6f49dd2d',16);
printf('q = %s<br>', $q);
//$a = gmp_init('9e960315 afad2538 06755984 8ebe2cd4 00c8774a b4b6270a a49e5093 6ac3d849 869582d4 6f7c8837 04d648be 5b142aa6 afde2127 b50d50f2 2ab5aab1 ce23e21c', 16);
$a = gmp_init('9e960315 00c8774a 869582d4 afde2127 afad2538 b4b6270a 6f7c8837 b50d50f2 06755984 a49e5093 04d648be 2ab5aab1 8ebe2cd4 6ac3d849 5b142aa6 ce23e21c', 16);
printf('a = %s<br>', $a);

$x = gmp_init('30363145 38303830 34363045 42353244 35324234 31413237 38324331 38443046', 16);
printf('x = %s<br>', $x);
//$k = gmp_init('90f3a564 11b7105c 439242f5 64e4f539 186ebb22 0807e636 4c8e2238 2df4c72a', 16);
$k = gmp_init('90f3a564 439242f5 186ebb22 4c8e2238 11b7105c 64e4f539 0807e636 2df4c72a', 16);
printf('k = %s<br>', $k);
//$h = gmp_init('35344541 32454236 44313445 34373139 43363345 37414342 34454136 31454230', 16);

$y = gmp_powm($a,$x, $p);
printf('y = %s<br><br>', $y);

$r = gmp_powm($a,$k, $p);
$r = gmp_mod($r, $q);
//s = (х * r + k (Н(m))) mod q.
$s = gmp_mod(gmp_add(gmp_mul($x,$r), gmp_mul($k,$h)), $q);
printf('(r, s) = (%s, %s) <br>', $r, $s);
//(r,s) Проверка подписи

$v = gmp_powm($h,gmp_sub($q,2), $q);
$z1 = gmp_mod(gmp_mul($s,$v),$q);
$z2 = gmp_mod(gmp_mul(gmp_sub($q,$r),$v),$q);
//u=((аz1 *уz2 )modр)modq
$u = gmp_mod(gmp_mod(gmp_mul(gmp_powm($a, $z1, $p), gmp_powm($y,$z2, $p)), $p), $q);
printf('Проверка подписи: r = %s', $u);

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