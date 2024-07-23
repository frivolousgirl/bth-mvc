<?php

namespace App\Card5;

class GameState
{
    private array $states = [
        "ANTE",
        "DEALING",
        "FIRST_BETTING_ROUND",
        "DRAW",
        "SECOND_BETTING_ROUND",
        "SHOWDOWN"
    ];
    private int $currentState = 0;

    public function getState(): string
    {
        return $this->states[$this->currentState];
    }

    public function nextState(): string
    {
        $this->currentState = ($this->currentState + 1) % count($this->states);
        return $this->getState();
    }

    public function setState(string $state): void
    {
        $index = array_search($state, $this->states);
        if ($index !== false) {
            $this->currentState = $index;
        }
    }
}
