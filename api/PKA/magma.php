<?php

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
//$inputKey = $request['key'] ?? null;

//$inputText = 'Прив';
$inputTextList = mb_str_split($inputText);

$block = 32;

//Докидываем данные
$needAddBits = ($block/16 - (count($inputTextList) % ($block/16))) % ($block/16);
if($needAddBits !== 0) {
    for($i=0;$i<$needAddBits;$i++) {
        $inputTextList[] = ' ';
    }
}

//1 буква это 2 байта или ffff или 16 битов, f - 4 бита
//Блок 32 бита - 2 буквы, 64 - 4 буквы
//8 4-х битовых последовательностей


//Последовательность текста в 16-ричном виде
$inputTextHex = '';
foreach ($inputTextList as $letter) {
    $unicodePointLetter = mb_ord($letter);
    $hexLetter = dechexCustom($unicodePointLetter);
    $inputTextHex .= $hexLetter;
}

//Зашифровка по s блокам
$j = 1;
$inputTextHexList = mb_str_split($inputTextHex);
$outputTextHex = '';
foreach ($inputTextHexList as $hex) {
    $outputTextHex .= executeSBlock($hex,$j);

    $j++;
    if($j === 8) {
        $j=1;
    }
}

//Перевести в десятичную и в буквы
$outputTextHexList = mb_str_split($outputTextHex, 4);
$outputText = '';
foreach ($outputTextHexList as $key => $hex) {
    $dec = hexdec($hex);
    print_r($key." Unicode: ".$dec."<br>");
    $outputText .= mb_chr($dec);
}

print_r("Шифротекст:<br>");
print_r($outputText . "<br>");

//Расшифровка:
$outputTextList = mb_str_split($outputText);

//Последовательность текста в 16-ричном виде
$outputTextHex = '';
foreach ($outputTextList as $letter) {
    $unicodePointLetter = mb_ord($letter);
    $hexLetter = dechexCustom($unicodePointLetter);
    $outputTextHex .= $hexLetter;
}

//Расшифровка по s блокам
$j = 1;
$outputTextHexList = mb_str_split($outputTextHex);
$decryptTextHex = '';
foreach ($outputTextHexList as $hex) {
    $decryptTextHex .= decryptSblock($hex,$j);

    $j++;
    if($j === 8) {
        $j=1;
    }
}

//Перевести в десятичную и в буквы
$decryptTextHexList = mb_str_split($decryptTextHex, 4);
$decryptText = '';
foreach ($decryptTextHexList as $key => $hex) {
    $dec = hexdec($hex);
    print_r($key." Unicode: ".$dec."<br>");
    $decryptText .= mb_chr($dec);
}


print_r("Расшифрованный текст:<br>");
print_r($decryptText);

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

function dechexCustom(int $unicodePoint): string {
    $hexLetter = dechex($unicodePoint);
    if (mb_strlen($hexLetter) === 1) {
        $hexLetter = '000'.$hexLetter;
    } else if (mb_strlen($hexLetter) === 2) {
        $hexLetter = '00'.$hexLetter;
    } else if (mb_strlen($hexLetter) === 3) {
        $hexLetter = '0'.$hexLetter;
    }

    return $hexLetter;
}