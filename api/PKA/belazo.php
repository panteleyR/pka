<?php

use function MOK\helpers\mod;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? null;
if (isset($request['key']) && $request['key'] === '') {
    $request['key'] = null;
}
$inputKey = $request['key'] ?? 'ЗОНД';
$inputTextList = mb_str_split(mb_strtoupper($inputText));
$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я', ' '
];
$n=count($alphabet);
foreach ($alphabet as $key => $item) {
    $alphabetNew[$key+1] = $item;
}
$alphabet = $alphabetNew;

$getI = function (string $letter) use ($alphabet): int {
    foreach ($alphabet as $key => $item) {
        if($item === $letter) {
            return $key;
        }
    }
    throw new Exception('getI');
};

$cryptText = '';
$keyList = mb_str_split(mb_strtoupper($inputKey));
foreach ($keyList as $key => $keyValue) {
    $keyListNew[$key+1] = $keyValue;
}
$keyList = $keyListNew;

$j = 1;
foreach ($inputTextList as $x) {
    $i = $getI($x);
    $jAlphabet = $getI($keyList[$j]);
    $resNum = ($i+$jAlphabet-1) % $n;
    $cryptText .= $alphabet[$resNum !== 0 ? $resNum : $n];

    $j = ($j+1) % count($keyList);
    $j = $j === 0 ? count($keyList) : $j;
}

print_r("Ключ: <br>");
print_r($inputKey."<br>");
print_r("Шифр: <br>");
print_r($cryptText."<br>");


$cryptTextList = mb_str_split(mb_strtoupper($cryptText));
$j = 1;
$decryptText = '';
foreach ($cryptTextList as $letter) {
    $i = $getI($letter);
    $jAlphabet = $getI($keyList[$j]);
    $resNum = mod($i-$jAlphabet+1, $n);
    $decryptText .= $alphabet[$resNum !== 0 ? $resNum : $n];

    $j = ($j+1) % count($keyList);
    $j = $j === 0 ? count($keyList) : $j;
}

print_r("Расшифровка: <br>");
print_r($decryptText."<br>");
