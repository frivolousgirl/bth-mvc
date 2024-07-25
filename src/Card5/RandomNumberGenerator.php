<?php

namespace App\Card5;

class RandomNumberGenerator
{
    public function generate(int $min, int $max): int
    {
        return rand($min, $max);
    }
}