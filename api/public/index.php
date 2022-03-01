<?php

require __DIR__ . '/../vendor/autoload.php';


if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else $link = "http";

// Here append the common URL characters.
$link .= "://";

// Append the host(domain name, ip) to the URL.
$link .= $_SERVER['HTTP_HOST'];

// Append the requested resource location to the URL
$link .= $_SERVER['REQUEST_URI'];
$link = parse_url($link);
$path = $link['path'];
$request = $_REQUEST;
if (isset($request['inputText']) && $request['inputText'] === '') {
    return;
}
if (isset($request['key']) && $request['key'] === '') {
    $request['key'] = null;
}

//print_r($path);

if ($path === '/api/crypt' || $path === '/api/crypt/') {
    header('Content-Type: application/json; charset=utf-8');
    print_r(json_encode([
        ['name' => 'Атбаш', 'code' => 'atbash', 'key' => false],
        ['name' => 'Цезарь', 'code' => 'caeser', 'key' => false],
        ['name' => 'Полибий', 'code' => 'polibiy', 'key' => false],
        ['name' => 'Тритемий', 'code' => 'tritemiy', 'key' => false],
        ['name' => 'Белазо', 'code' => 'belazo', 'key' => true],
        ['name' => 'Виженер', 'code' => 'vizhener', 'key' => true],
        ['name' => 's-блок магма перестановка', 'code' => 'magma', 'key' => false],
        ['name' => 'Матричный', 'code' => 'matrix', 'key' => false],
        ['name' => 'Магма', 'code' => 'magmafeystel', 'key' => false],
        ['name' => 'Магма гаммирование', 'code' => 'magmagamma', 'key' => false],
        ['name' => 'RSA ЦП', 'code' => 'rsads', 'key' => false],
        ['name' => 'Elgamal ЦП', 'code' => 'elgamalds', 'key' => false],
        ['name' => 'ГОСТ 34.10.12 ЦП', 'code' => 'gost341012', 'key' => false],
        ['name' => 'ГОСТ 34.10.94 ЦП', 'code' => 'gost341094', 'key' => false],
        ['name' => 'RSA', 'code' => 'rsa', 'key' => false],
        ['name' => 'Elgamal', 'code' => 'elgamal', 'key' => false],
        ['name' => 'Diffie Hellman', 'code' => 'diffiehellman', 'key' => false],
        ['name' => 'Elgamal ECC', 'code' => 'elgamalEcc', 'key' => false],
    ]));
} else if ($path === '/api/crypt/atbash') {
    require __DIR__ . '/../PKA/atbash.php';
} else if ($path === '/api/crypt/caeser') {
    require __DIR__ . '/../PKA/caeser.php';
} else if ($path === '/api/crypt/polibiy') {
    require __DIR__ . '/../PKA/polibiy.php';
} else if ($path === '/api/crypt/tritemiy') {
    require __DIR__ . '/../PKA/tritemiy.php';
} else if ($path === '/api/crypt/belazo') {
    require __DIR__ . '/../PKA/belazo.php';
} else if ($path === '/api/crypt/vizhener') {
    require __DIR__ . '/../PKA/vizhener.php';
} else if ($path === '/api/crypt/magma') {
    require __DIR__ . '/../PKA/magma.php';
} else if ($path === '/api/crypt/matrix') {
    require __DIR__ . '/../PKA/matrix.php';
} else if ($path === '/api/crypt/magmafeystel') {
    require __DIR__ . '/../PKA/magmafeystel.php';
} else if ($path === '/api/crypt/magmagamma') {
    require __DIR__ . '/../PKA/magmagamma.php';
} else if ($path === '/api/crypt/rsads') {
    require __DIR__ . '/../PKA/rsads.php';
} else if ($path === '/api/crypt/elgamalds') {
    require __DIR__ . '/../PKA/elgamalds.php';
} else if ($path === '/api/crypt/gost341012') {
    require __DIR__ . '/../PKA/gost341012.php';
} else if ($path === '/api/crypt/gost341094') {
    require __DIR__ . '/../PKA/gost341094.php';
} else if ($path === '/api/crypt/rsa') {
    require __DIR__ . '/../PKA/rsa.php';
} else if ($path === '/api/crypt/elgamal') {
    require __DIR__ . '/../PKA/elgamal.php';
} else if ($path === '/api/crypt/diffiehellman') {
    require __DIR__ . '/../PKA/diffiehellman.php';
} else if ($path === '/api/crypt/elgamalEcc') {
    require __DIR__ . '/../PKA/elgamalEcc.php';
//} else if ($path === '/api/crypt/') {
//} else if ($path === '/api/crypt/') {
} else {
    print_r('Шифры'.'<br>');
    print_r('Основной входящий параметр inputText -- текст, который хотим зашифровать'.'<br>');
    print_r('К примеру для шифра атбаш для запроса использовать урл /api/crypt/atbash?inputText=Зашифруй меня'.'<br>');
    print_r('Используется исключительно гет запрос, так что можешь для теста просто в браузере этот урл прописать, чтобы увидеть результат шифрования'.'<br>');
    print_r('Иногда используется доп параметры, например, ключ шифрования, их я просто справа от урла прописываю с примером значения'.'<br>');
    print_r('Атбаш /api/crypt/atbash'.'<br>');
    print_r('Цезаря /api/crypt/caeser'.'<br>');
    print_r('Полибий /api/crypt/polibiy'.'<br>');
    print_r('Тритемий /api/crypt/tritemiy key=ЗОНД'.'<br>');
    print_r('Виженер /api/crypt/vizhener key=А'.'<br>');
    print_r('Магма /api/crypt/magma'.'<br>');
    print_r('Матричный способ /api/crypt/matrix'.'<br>');
    print_r(''.'<br>');
    print_r(''.'<br>');
    print_r(''.'<br>');
    print_r(''.'<br>');
}
