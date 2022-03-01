<?php

namespace MOK\helpers;

function mod(int $number, int $mod): int
{
    $result = $number % $mod;
    if($result < 0) {
        return $mod+$result;
    }

    return $result;
}

function pre($value, bool $check = true): void
{
    print_r("<pre>");
    print_r($value);
    print_r("</pre>");
    if ($check === true) {
        exit;
    }
}