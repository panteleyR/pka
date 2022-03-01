<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;

//$inputText = $request['inputText'] ?? null;
//$inputKey = $request['key'] ?? null;

//Ключ 256 бит
//$inputKey = '5052cf0a3c02bb874d3befaaec7affe7bb03ad1fd24b855de2435e8f81961415';

//$p = gmp_init('7');
$a = gmp_init('57896044618658097711785492504343953926634992332820282019728792003956564821041');
print('Число a='.$a."<br>");
//$q = gmp_init('13');
$n = gmp_init('3623986102229003635907788753683874306021320925534678605086546150450856166624002482588482022271496854025090823603058735163734263822371964987228582907372403');
print('Простое число n='.$n."<br><br>");

$ka = gmp_random_range(gmp_init(2), $n);
print('Секретный ключ Ka: '.gmp_strval($ka, 16)."<br>");
$kb = gmp_random_range(gmp_init(2), $n);
print('Секретный ключ Kb: '.gmp_strval($kb, 16)."<br><br>");

$ya = gmp_powm($a, $ka, $n);
print('Открытый ключ Ya: '.gmp_strval($ya, 16)."<br>");
$yb = gmp_powm($a, $kb, $n);
print('Открытый ключ Yb: '.gmp_strval($yb, 16)."<br><br>");

$k = gmp_powm($yb,$ka, $n);
print('Закрытый ключ на стороне А: '.gmp_strval($k, 16)."<br>");
$k = gmp_powm($ya,$kb, $n);
print('Закрытый ключ на стороне B: '.gmp_strval($k, 16)."<br>");

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
