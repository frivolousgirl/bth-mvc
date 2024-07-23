<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\HandEvaluator;
use App\Card5\DeckOfCards;

class PlayerManager
{
    private array $players;

    public function __construct(array $playerNames, HandEvaluator $handEvaluator)
    {
        $this->players = array_map(fn ($name) => new Player($name, $handEvaluator), $playerNames);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function dealCards(DeckOfCards $deck): void
    {
        foreach ($this->players as $player) {
            $player->receiveCards($deck->deal(5));
        }
    }

    public function discardAndDraw(int $playerId, array $cardsToDiscard, DeckOfCards $deck): void
    {
        $this->players[$playerId]->discardAndDraw($cardsToDiscard, $deck);
    }

    public function fold(int $playerId): void
    {
        $this->players[$playerId]->fold();
    }

    public function reset(): void
    {
        foreach ($this->players as $player) {
            $player->reset();
        }
    }
}
