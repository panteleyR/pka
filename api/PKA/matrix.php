<?php

use function MOK\helpers\pre;

$request = $_REQUEST;

$inputText = $request['inputText'] ?? null;
$inputTextList = mb_str_split(mb_strtoupper($inputText));
if (isset($request['key']) && $request['key'] === '') {
    $request['key'] = null;
}
//Пример ?inputText=ЗАБАВА&key=(1,4,8),(3,7,2),(6,9,5)
$inputKey = '(1,4,8),(3,7,2),(6,9,5)';

$alphabet = [
    'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
];
foreach ($alphabet as $key => $item) {
    $alphabetNew[$key+1] = $item;
}
$alphabet = $alphabetNew;
$n=count($alphabet);

$getI = function (string $letter) use ($alphabet): int {
    foreach ($alphabet as $key => $item) {
        if($item === $letter) {
            return $key;
        }
    }
    print_r($letter);
    throw new Exception('getI');
};

$inputTextNumberList = [];
foreach ($inputTextList as $letter) {
    $inputTextNumberList[] = $getI($letter);
}

$keyMatrixArray = array_filter(explode(')', $inputKey));

$keyMatrixArrayNew = [];
foreach ($keyMatrixArray as $key => $item) {
    $item = trim($item);
    $item = trim($item, '(,)');
    $keyMatrixArrayNew[$key] = explode(',', $item);
}
$keyMatrixArray = $keyMatrixArrayNew;

$keyMatrix = new Matrix($keyMatrixArray, count($keyMatrixArray[0]), count($keyMatrixArray));

$result = [];
foreach ($inputTextNumberList as $key => $item) {
    $i = $key+1;
    $list[] = [$item];
    if ($i % $keyMatrix->sizeColumn() === 0) {
        $C = new Matrix($list, 1, $keyMatrix->sizeColumn());
        $result = array_merge($result, $keyMatrix->multipleTo($C)->getColumnData()[0]);
        $list = [];
    }
}

$resultText = '';
foreach ($result as $letterI) {
    $resultText .= $letterI.' ';
}
print_r('Зашифрованный текст:'."<br>");
print_r($resultText."<br>");

$resultText = '';
foreach ($result as $letterI) {
    $letterI = $letterI % $n;
    $letterI = $letterI !== 0 ? $letterI : $n;
    $resultText .= $alphabet[$letterI];
}

//print_r('Расшифрованный текст:'."<br>");
//print_r($resultText."<br>");


//$a = [[3,5], [2,1]];
//$b = [[8,2,3], [1,7,2]];
//$matrixA = new Matrix($a, 2,2);
//$matrixB = new Matrix($b, 3,2);
//$matrixA->multipleTo($matrixB);

class Matrix
{
    protected array $matrixData = [];
    protected int $sizeX;
    protected int $sizeY;

    public function __construct(array $matrixData, int $sizeX, int $sizeY)
    {
        if (count($matrixData) !== $sizeY) {
            throw new Exception('$sizeY matrix');
        }
        foreach ($matrixData as $row) {
            if (count($row) !== $sizeX) {
                throw new Exception('$sizeX matrix');
            }
        }
        $this->matrixData = $matrixData;
        $this->sizeX = $sizeX;
        $this->sizeY = $sizeY;
    }

    public function sizeColumn(): int
    {
        return $this->sizeX;
    }

    public function sizeRow(): int
    {
        return $this->sizeY;
    }

    public function data(): array
    {
        return $this->matrixData;
    }

    public function getColumnData(): array
    {
        $result = [];
        for($i = 0; $i<$this->sizeColumn(); $i++) {
            for($j = 0; $j<$this->sizeRow(); $j++) {
                $result[$i][$j] = $this->matrixData[$j][$i];
            }
        }
        return $result;
    }

    public function multipleTo(Matrix $matrixB): Matrix
    {
        if ($this->sizeColumn() !== $matrixB->sizeRow()) {
            var_dump($matrixB->sizeRow());exit;
            throw new Exception('multiple matrix');
        }

        $result = [];
        foreach ($this->matrixData as $rowKey => $row) {
            foreach ($matrixB->getColumnData() as $key => $columnB) {
                $value = 0;
                foreach ($row as $keyColumn => $columnValue) {
                    $value += $columnB[$keyColumn]*$columnValue;
                }
                $result[$rowKey][$key] = $value;
            }
        }

        return new Matrix($result, count($result[0]), count($result));
    }
}

print_r('Расшифрованный текст:'."<br>");
print_r($inputText."<br>");
