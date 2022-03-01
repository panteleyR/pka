<?php

namespace MOK\Model;

class EllipticCurve
{
    protected array $quadraticList = [];

    public function __construct(
        protected int $mod,
        protected int $a,
        protected int $b,
    ) {}

    public function quadraticY(int $x): int
    {
        return ($x*$x*$x + $this->a*$x + $this->b) % $this->mod;
    }

    public function getY(int $x): array
    {
        $quadraticY = $this->quadraticY($x);
        $y = [];
        foreach ($this->getQuadraticList() as $key => $el) {
            if ($el === $quadraticY) {
                $y[] = $key;
            }
        }
        return $y;
    }

    public function getQuadraticList(): array
    {
        if ($this->quadraticList === []) {
            for ($i = 0; $i < $this->mod; $i++) {
                $this->quadraticList[$i] = $i * $i % $this->mod;
            }
        }
        return $this->quadraticList;
    }

    public function getPoints(): array
    {
        $points = [];
        for($x = 0; $x < $this->mod; $x++) {
            $points[$x] = $this->getY($x);
        }
        $points = array_filter($points);
        $pointsResult = [];
        foreach ($points as $x => $yList) {
            foreach ($yList as $y) {
                $pointsResult[] = new Point($x, $y, $this->mod);
            }
        }
        return $pointsResult;
    }

    public function getPoryadok(): int
    {
        $points = $this->getPoints();
        $pointsValuableCount = 0;
        $pointsNullableCount = 0;
        foreach ($points as $point) {
            if (!$point instanceof Point) {
                throw new \Exception('instanceof trouble getPoryadok');
            }

            if ($point->getY() !== 0) {
                $pointsValuableCount += 1;
            } else {
                $pointsNullableCount += 1;
            }
        }
        $pointsValuableCount = $pointsValuableCount / 2;
        if(is_int($pointsValuableCount) === false) {
            throw new \Exception('$pointsValuableCount is not INT');
        }

        $n = $this->mod + 1 + ($pointsValuableCount - ($this->mod - $pointsValuableCount - $pointsNullableCount));
        return $n;
    }

//    public function getPointsPoryadok()
//    {
//        $n = $this->getPoryadok();
//        $points = $this->getPoints();
//        foreach ($points as $pointX => $pointYList) {
//
//        }
//    }

    public function getMod(): int
    {
        return $this->mod;
    }

    public function getA(): int
    {
        return $this->a;
    }

    public function getB(): int
    {
        return $this->b;
    }
}
