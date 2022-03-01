<?php

namespace MOK\Model;

class NullablePoint extends Point
{
    public function __construct()
    {
        $this->x = 0;
        $this->y = 0;
        $this->mod = 0;
    }
}
