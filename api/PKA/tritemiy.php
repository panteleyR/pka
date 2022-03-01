<?php

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
$inputText = str_replace(' ', '', $inputText);
$inputTextList = mb_str_split(mb_strtoupper($inputText));
$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
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
$j = 1;
foreach ($inputTextList as $x) {
    if($x === ' ') {
        $cryptText .= ' ';
        continue;
    }
    $i = $getI($x);
    $resNum = ($i+$j-1) % $n;
    $cryptText .= $alphabet[$resNum !== 0 ? $resNum : $n];

    $j = $j+1;
}

print_r($cryptText);