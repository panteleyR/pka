<?php

namespace MOK\Model;

class Point
{
    public function __construct(
        protected int $x,
        protected int $y,
        protected int $mod
    ) {}

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getMod(): int
    {
        return $this->mod;
    }
}