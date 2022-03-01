<?php

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
$inputText = str_replace(' ', '', $inputText);
$inputTextList = mb_str_split(mb_strtoupper($inputText));
$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
];
$n = count($alphabet);
//print_r(mb_strtoupper($inputText));exit;
$getI = function (string $letter) use ($alphabet): int {
    foreach ($alphabet as $key => $item) {
        if($item === $letter) {
            return $key+1;
        }
    }
    throw new Exception('getI');
};

$cryptText = '';
foreach ($inputTextList as $x) {
    $i = $getI($x);
    $crypting = $n - $i + 1;
    $y = $alphabet[$crypting-1];
    $cryptText .= $y;
}

print_r("Шифр: <br>");
print_r($cryptText."<br>");


$cryptTextList = mb_str_split($cryptText);
$decryptText = '';
foreach ($cryptTextList as $x) {
    $i = $getI($x);
    $crypting = $n - $i + 1;
    $y = $alphabet[$crypting-1];
    $decryptText .= $y;
}

print_r("Расшифровка: <br>");
print_r($decryptText."<br>");