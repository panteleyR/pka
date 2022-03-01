<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? null;
$inputKey = $request['key'] ?? null;

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
$m = TextHexBinConverter::convertTextToHex($m);
$zeroHero = TextHexBinConverter::getHexStartZero($m);
$m = gmp_init($m, 16);
//print('Шифротекст: '.gmp_strval($m, 16)."<br>");

$c = gmp_powm($m,$e,$n);
$c = $zeroHero.gmp_strval($c,16);
print('Шифротекст: '.$c."<br>");

//Расшифровка
$zeroHero = TextHexBinConverter::getHexStartZero($c);
$c = gmp_init($c,16);
$m = gmp_powm($c,$d, $n);
$m = $zeroHero.gmp_strval($m, 16);
$m = TextHexBinConverter::convertHexToText($m);
print('Расшифровка: '.$m."<br>");

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
