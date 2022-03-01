<?php

namespace MOK\Services;

use MOK\Model\EllipticCurve;
use MOK\Model\NullablePoint;
use MOK\Model\Point;
use function MOK\helpers\mod;

class PointCalculate
{
    public function __construct(
        protected EllipticCurve $ec,
        protected bool $outputMode = false,
    ) {}

    public function sum(Point $pointOne, Point $pointTwo): Point
    {
        if ($pointTwo instanceof NullablePoint) {
            return $pointOne;
        }

        if($pointOne->getX() === $pointTwo->getX() && $pointOne->getY() === $pointTwo->getY()) {
            return $this->sumSame($pointOne);
        }

        if($pointOne->getX() === $pointTwo->getX()) {
            return new NullablePoint();
        }

        $L = $this->countLambda($pointOne, $pointTwo);
        $x3 = mod($L*$L - $pointOne->getX() - $pointTwo->getX(), $this->ec->getMod());
        $this->outputMode && printf('x3 = %s*%s - %s - %s = %s' . PHP_EOL, $L, $L, $pointOne->getX(), $pointTwo->getX(), $x3);
        $y3 = mod($L*($pointOne->getX() - $x3) - $pointOne->getY(), $this->ec->getMod());
        $this->outputMode && printf('y3 = %s*(%s - %s) - %s = %s' . PHP_EOL, $L, $pointOne->getX(), $x3, $pointOne->getY(), $y3);
        return new Point($x3, $y3, $this->ec->getMod());
    }

    public function countPoryadok(Point $point): int
    {
        $pointSum = $point;
        for($i = 1; $i <= $this->ec->getPoryadok(); $i++) {
            $this->outputMode && printf('%sP:' . PHP_EOL, $i + 1);
            $pointSum = $this->sum($point, $pointSum);
            $this->outputMode && printf('(%s, %s)' . PHP_EOL, $pointSum->getX(), $pointSum->getY());
            if ($pointSum instanceof NullablePoint) {
                return $i + 1;
            }
        }

        throw new \Exception('fuck');
    }

    public function multiplyByN(Point $point, int $n): Point
    {
        $pointSum = $point;
        $this->outputMode && printf('P = (%s, %s)' . PHP_EOL, $pointSum->getX(), $pointSum->getY());

        for($i = 2; $i <= $n; $i++) {
            $this->outputMode && printf('%sP:' . PHP_EOL, $i);
            $pointSum = $this->sum($point, $pointSum);
            $this->outputMode && printf('(%s, %s)' . PHP_EOL, $pointSum->getX(), $pointSum->getY());
        }

        return $pointSum;
    }

    protected function countLambda(Point $pointOne, Point $pointTwo): int
    {
        $up = mod($pointTwo->getY() - $pointOne->getY(), $this->ec->getMod());
        $down = mod($pointTwo->getX() - $pointOne->getX(), $this->ec->getMod());
        $this->outputMode && printf('L = %s-%s / %s-%s = %s/%s = %s' . PHP_EOL, $pointTwo->getY(),$pointOne->getY(),$pointTwo->getX(),$pointOne->getX(), $up, $down, $this->findDiv($up, $down));
        return $this->findDiv($up, $down);
    }

    protected function sumSame(Point $point): Point
    {
        if ($point->getY() === 0) {
            return new NullablePoint();
        }

        $L = $this->countSameLambda($point);
        $x3 = mod($L*$L - 2*$point->getX(), $this->ec->getMod());
        $this->outputMode && printf('x3 = %s*%s - 2*%s = %s' . PHP_EOL, $L, $L, $point->getX(), $x3);
        $y3 = mod($L*($point->getX() - $x3) - $point->getY(), $this->ec->getMod());
        $this->outputMode && printf('y3 = %s*(%s - %s) - %s = %s' . PHP_EOL, $L, $point->getX(), $x3, $point->getY(), $y3);
        return new Point($x3, $y3, $this->ec->getMod());
    }

    protected function countSameLambda(Point $point): int
    {
        $up = mod(3*$point->getX()*$point->getX() + $this->ec->getA(), $this->ec->getMod());
        $down = mod(2*$point->getY(), $this->ec->getMod());
        $this->outputMode && printf('L = 3*%s*%s + %s / 2*%s = %s/%s = %s' . PHP_EOL, $point->getX(),$point->getX(),$this->ec->getA(),$point->getY(), $up, $down, $this->findDiv($up, $down));

        return $this->findDiv($up, $down);
    }

    public function findDiv(int $first, int $div): int
    {
        $mod = $this->ec->getMod();
        for ($i = 0; $i<$mod; $i++) {
            $result = mod($i*$div, $mod);
            if ($result === $first) {
                return $i;
            }
        }

        throw new \Exception('findDiv not found');
    }
}
