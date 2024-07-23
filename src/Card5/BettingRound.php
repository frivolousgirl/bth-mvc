<?php

namespace App\Card5;

class BettingRound
{
    private array $bets = [];
    private int $lastBet = -1;

    public function addBet(int $bet): void
    {
        $this->bets[] = $bet;
        $this->lastBet = $bet;
    }

    public function isBettingRoundOver(int $numberOfPlayers): bool
    {
        return count($this->bets) === $numberOfPlayers &&
            count(array_unique($this->bets)) === 1 &&
            $this->lastBet !== -1;
    }

    public function getLastBet(): int
    {
        return $this->lastBet;
    }

    public function getBets(): array
    {
        return $this->bets;
    }

    public function hasBets(): bool
    {
        return !empty($this->bets);
    }

    public function reset(): void
    {
        $this->bets = [];
        $this->lastBet = -1;
    }
}
