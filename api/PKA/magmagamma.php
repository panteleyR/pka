<?php
use function MOK\helpers\pre;
use function MOK\helpers\mod;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
//$inputKey = $request['key'] ?? null;

//Ключ 256 бит
$inputKey = '5052cf0a3c02bb874d3befaaec7affe7bb03ad1fd24b855de2435e8f81961415';

$synchroMess = '2f24ce422f24ce42';

//64 бита
//Докидываем данные

//1 буква это 2 байта(unicode кириллица) или ffff или 16 битов, f - 4 бита
//Подблок 32 бита - 2 буквы, 64 - 4 буквы
//8 4-х битовых последовательностей


//Последовательность текста в 16-ричном виде
$inputTextHex = TextHexBinConverter::convertTextToHex($inputText);

//Разделяем текст на блоки

$magma = new MagmaGamma($inputTextHex, $inputKey, $synchroMess);

$resHex = $magma->encrypt();

print_r('Ключ: '.$inputKey."<br>");
print_r('шифр:'."<br>");
print_r(TextHexBinConverter::convertHexToText($resHex)."<br>");

$magma = new MagmaGamma($resHex, $inputKey, $synchroMess);
$resHex = $magma->decrypt();

print_r('Расшифровка:'."<br>");
print_r(TextHexBinConverter::convertHexToText($resHex)."<br>");


function decryptSblock(string $hex, int $j): string {
    $hexAlphabet = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
    $sBlockList = [
        1 => ['c','4','6','2','a','5','b','9','e','8','d','7','0','3','f','1'],
        2 => ['6','8','2','3','9','a','5','c','1','e','4','7','b','d','0','f'],
        3 => ['b','3','5','8','2','f','a','d','e','1','7','4','c','9','6','0'],
        4 => ['c','8','2','1','d','4','f','6','7','0','a','5','3','e','9','b'],
        5 => ['7','f','5','a','8','1','6','d','0','9','3','e','b','4','2','c'],
        6 => ['5','d','f','6','9','2','c','a','b','7','8','1','4','3','e','0'],
        7 => ['8','e','2','5','6','9','1','c','f','4','b','0','d','a','3','7'],
        8 => ['1','7','e','d','0','5','8','3','4','f','a','6','9','c','b','2'],
    ];
    $hexI = (function (string $hex) use ($sBlockList, $j): int {
        foreach ($sBlockList[$j] as $i => $alpha) {
            if ($hex === $alpha) {
                return $i;
            }
        }
        throw new Exception('decryptSblock');
    })($hex);

    return $hexAlphabet[$hexI];
}

function executeSBlock(string $hex, int $j): string {
    $hexAlphabet = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
    $hexI = (function (string $hex) use ($hexAlphabet): int {
        foreach ($hexAlphabet as $i => $alpha) {
            if ($hex === $alpha) {
                return $i;
            }
        }
        throw new Exception('executeSBlock');
    })($hex);
    $sBlockList = [
        1 => ['c','4','6','2','a','5','b','9','e','8','d','7','0','3','f','1'],
        2 => ['6','8','2','3','9','a','5','c','1','e','4','7','b','d','0','f'],
        3 => ['b','3','5','8','2','f','a','d','e','1','7','4','c','9','6','0'],
        4 => ['c','8','2','1','d','4','f','6','7','0','a','5','3','e','9','b'],
        5 => ['7','f','5','a','8','1','6','d','0','9','3','e','b','4','2','c'],
        6 => ['5','d','f','6','9','2','c','a','b','7','8','1','4','3','e','0'],
        7 => ['8','e','2','5','6','9','1','c','f','4','b','0','D','a','3','7'],
        8 => ['1','7','e','d','0','5','8','3','4','f','a','6','9','c','b','2'],
    ];

    return $sBlockList[$j][$hexI];
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

class MagmaGamma extends Magma
{
    protected string $synchroMess;

    public function __construct(string $hexText, string $keyHex, string $synchroMess)
    {
        $this->synchroMess = $synchroMess;
        parent::__construct($hexText, $keyHex);
    }

    public function encrypt(): string
    {
        $gamma = $this->createGamma();
        $cipherText = '';

        for($i=0; $i<strlen($this->hexText); $i++) {
            $cipherText .= dechex(hexdec($this->hexText[$i]) ^ hexdec($gamma[$i]));
        }

        return $cipherText;
    }

    public function decrypt(): string
    {
        $gamma = $this->createGamma();
        $plainText = '';

        for($i=0; $i<strlen($this->hexText); $i++) {
            $plainText .= dechex(hexdec($this->hexText[$i]) ^ hexdec($gamma[$i]));
        }

        return $plainText;
    }

    public function createGamma(): string
    {
        $gamma = '';
        $hexN3N4 = $this->encryptBlock($this->synchroMess);
        $hexN3N4List = TextHexBinConverter::convertHexToBlockList($hexN3N4, 32);

        $C1 = 16843009;
        $C2 = 16843012;

        $N3 = hexdec($hexN3N4List[0]);
        $N4 = hexdec($hexN3N4List[1]);
        while (strlen($gamma) < strlen($this->hexText)) {
            $N3 = ($N3 + $C1) % pow(2, 32);
            $N4 = ($N4 + $C2) % (pow(2, 32) - 1);

            $N1 = TextHexBinConverter::dechexCustom($N3, 32);
            $N2 = TextHexBinConverter::dechexCustom($N4, 32);
            $N1N2 = $this->encryptBlock($N1.$N2);
            $gamma .= $N1N2;
        }

        $gamma = mb_substr($gamma, 0, strlen($this->hexText));

        return $gamma;
    }
}

class Magma
{
    protected int $blockSize = 64;
    protected string $hexText;
    protected string $hexKey;
    protected int $rounds = 32;

    public function __construct(string $hexText, string $keyHex)
    {
        $this->hexText = $hexText;
        $this->hexKey = $keyHex;
    }

    public function encrypt(): string
    {
        $blockList = TextHexBinConverter::convertHexToBlockList($this->hexText, $this->blockSize);

        $result = '';
        foreach ($blockList as $block) {
            $result .= $this->encryptBlock($block);
        }
        return $result;
    }

    public function decrypt(): string
    {

        $blockList = TextHexBinConverter::convertHexToBlockList($this->hexText, $this->blockSize);
        $result = '';
        foreach ($blockList as $block) {
            $result .= $this->decryptBlock($block);
        }
        return $result;
    }

    protected function encryptBlock(string $hexText): string
    {
        $keyCycleList = $this->generateKeyCycle();

        $blockList = TextHexBinConverter::convertHexToBlockList($hexText, 32);
        $blockL = $blockList[0];
        $blockR = $blockList[1];

        for($i = 0; $i < count($keyCycleList); $i++) {
            if(count($keyCycleList) === $i+1) {
                $key = $keyCycleList[$i];
                $newBlockR = $blockL;
                $blockL = $this->cycle($blockR, $blockL, $key);
                $blockR = $newBlockR;
                return $blockL.$blockR;
            }
            $key = $keyCycleList[$i];
            $newBlockL = $blockR;
            $blockR = $this->cycle($blockR, $blockL, $key);
            $blockL = $newBlockL;
        }

        return $blockL.$blockR;
    }

    protected function decryptBlock(string $hexText): string
    {
        $keyCycleList = $this->generateKeyCycle();
        $blockList = TextHexBinConverter::convertHexToBlockList($hexText, 32);

        $blockL = $blockList[0];
        $blockR = $blockList[1];

        for($i = (count($keyCycleList)-1); $i >= 0; $i--) {
            if(0 === $i) {
                $key = $keyCycleList[$i];
                $newBlockR = $blockL;
                $blockL = $this->decryptCycle($blockR, $blockL, $key);
                $blockR = $newBlockR;
                return $blockL.$blockR;
            }
            $key = $keyCycleList[$i];
            $newBlockL = $blockR;
            $blockR = $this->decryptCycle($blockR, $blockL, $key);
            $blockL = $newBlockL;
        }

        return $blockL.$blockR;
    }

    protected function cycle(string $blockR, string $blockL, string $key): string
    {
        $blockR = $this->L($blockR,$key);

        $blockR = $this->executeSBlock($blockR);
        $blockR = $this->bitShift($blockR);
        $blockR = $this->xorBlock($blockL, $blockR);

        return $blockR;
    }

    protected function decryptCycle(string $blockR, string $blockL, string $key): string
    {
        $blockR = $this->L($blockR,$key);

        $blockR = $this->decryptSblock($blockR);
        $blockR = $this->bitShift($blockR);
        $blockR = $this->xorBlock($blockL, $blockR);

        return $blockR;
    }


    protected function bitShift(string $blockL): string
    {
        $res = (hexdec($blockL) << 11) % pow(2,32);
        return TextHexBinConverter::dechexCustom($res, 32);
    }


    protected function xorBlock(string $blockR, string $blockL): string
    {
        $res = hexdec($blockR) ^ hexdec($blockL);
        return TextHexBinConverter::dechexCustom($res, 32);
    }

    protected function decryptSblock(string $blockL): string
    {
        $j=1;
        $outputTextHexList = mb_str_split($blockL);
        $decryptTextHex = '';
        foreach ($outputTextHexList as $hex) {
            $decryptTextHex .= decryptSblock($hex, $j);
            $j++;
            if($j === 8) {
                $j=1;
            }
        }

        return $decryptTextHex;
    }

    protected function executeSBlock(string $blockL): string
    {
        $j = 1;
        $inputTextHexList = mb_str_split($blockL);
        $outputTextHex = '';

        foreach ($inputTextHexList as $hex) {
            $outputTextHex .= executeSBlock($hex,$j);

            $j++;
            if($j === 8) {
                $j=1;
            }
        }

        return $outputTextHex;
    }

    protected function decryptL($blockL, $key): string
    {
        $res = mod((hexdec($blockL) - hexdec($key)), pow(2,32));
        $res = TextHexBinConverter::dechexCustom($res, 32);

        return $res;
    }
    protected function L($blockR, $key): string
    {

        $res = (hexdec($blockR) + hexdec($key)) % pow(2,32);
        $res = TextHexBinConverter::dechexCustom($res, 32);

        return $res;
    }

    public function generateKeyCycle(): array
    {
        $keyList = TextHexBinConverter::convertHexToBlockList($this->hexKey, 32);
        $cycleList = [];
        for ($i=0;$i<32;$i++) {
            if ($i<24) {
                $keyI = $i % count($keyList);
            } else {
                $keyI = count($keyList) - 1 - ($i % count($keyList));
            }
            $cycleList[] = $keyList[$keyI];
        }

        return $cycleList;
    }
}
