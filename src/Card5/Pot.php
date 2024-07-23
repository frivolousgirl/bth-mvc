<?php

namespace App\Card5;

class Pot
{
    private int $amount = 0;

    public function add(int $amount): void
    {
        $this->amount += $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function reset(): void
    {
        $this->amount = 0;
    }
}
