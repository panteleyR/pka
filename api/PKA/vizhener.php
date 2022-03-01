<?php

use function MOK\helpers\mod;
$request = $_REQUEST;

$inputText = $request['inputText'] ?? 'Тестовый текст';
if (isset($request['key']) && $request['key'] === '') {
    $request['key'] = null;
}
$inputKey = $request['key'] ?? 'К';
$inputKey = mb_str_split($inputKey)[0];
$inputKey = mb_strtoupper($inputKey);
$inputText = mb_strtoupper($inputText);
$inputTextList = mb_str_split($inputText);
$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я', ' '
];
$n=count($alphabet);
foreach ($alphabet as $key => $item) {
    $alphabetNew[$key+1] = $item;
}
$alphabet = $alphabetNew;

//print_r("<pre>");
//print_r($inputTextList);
//print_r("</pre>");exit;

$getI = function (string $letter) use ($alphabet): int {
    foreach ($alphabet as $key => $item) {
        if($item === $letter) {
            return $key;
        }
    }
    print_r($letter);
    throw new Exception('getI');
};

$gama = [$inputKey];
$encryptText = '';
foreach ($inputTextList as $key => $letter) {
    $t = $getI($letter);
    $s = $getI($gama[$key]);
    $encryptLetterI = mod($t+$s-1, $n);
    $encryptLetterI = $encryptLetterI !== 0 ? $encryptLetterI : $n;
    $encryptLetter = $alphabet[$encryptLetterI];
    $encryptText .= $encryptLetter;
//    $jAlphabet = $getI($keyList[$j]);
    $gama[] = $encryptLetter;
}

print_r("Ключ: <br>");
print_r($inputKey."<br>");
print_r("Шифр ключом-шифртекстом: <br>");
print_r($encryptText . "<br>");


$encryptTextList = mb_str_split($encryptText);

$gama = [$inputKey];
$decryptText = '';
foreach ($encryptTextList as $key => $letter) {
    $t = $getI($letter);
    $s = $getI($gama[$key]);
    $decryptLetterI = mod($t-$s+1, $n);
    $decryptLetterI = $decryptLetterI !== 0 ? $decryptLetterI : $n;
    $decryptLetter = $alphabet[$decryptLetterI];
    $decryptText .= $decryptLetter;
    $gama[] = $letter;
}
print_r("Расшифровка: <br>");
print_r($decryptText . "<br>");