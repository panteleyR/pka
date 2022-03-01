<?php

$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
//$inputText = str_replace(' ', '', $inputText);
$inputTextList = mb_str_split(mb_strtoupper($inputText));
$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я', ' '
];
$alphabetPolibiy = [];
foreach ($alphabet as $key => $letter) {
    $i = $key;
    $alphabetPolibiy[floor($i/6)+1][($i % 6) + 1] = $letter;
}

//print_r("<pre>");
//print_r($inputTextList);
//print_r("</pre>");exit;

$getPoint = function (string $letter) use ($alphabetPolibiy): string {
    foreach ($alphabetPolibiy as $i => $row) {
       foreach ($row as $j => $item) {
           if ($letter === $item) {
               return $i.$j;
           }
       }
    }
    throw new Exception('getPoint');
};

$cryptText = '';
foreach ($inputTextList as $x) {
//    if($x === ' ') {
//        continue;
//    }
    $point = $getPoint($x);
    $cryptText .= ' ' . $point;
}

print_r($cryptText);