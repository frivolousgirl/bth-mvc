<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\HandEvaluator;

class PlayerFactory
{
    private HandEvaluator $handEvaluator;

    public function __construct(HandEvaluator $handEvaluator)
    {
        $this->handEvaluator = $handEvaluator;
    }

    public function create(string $name): Player
    {
        return new Player($name, $this->handEvaluator);
    }
}