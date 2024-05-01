<?php

namespace App\Game21;

use App\Card\Card;

class Player
{
    /** @var Card[] */
    private $cards = [];

    public function __construct()
    {
        $this->init();
    }

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function countCards(): int
    {
        return count($this->cards);
    }

    /** @return array<Card> */

    public function getCards(): array
    {
        return $this->cards;
    }

    public function init(): void
    {
        $this->cards = [];
    }

    public function sumCardValues(): int
    {
        $sum = 0;
        foreach ($this->cards as $card) {
            $sum += Card::valueFromRank($card->rank);
        }
        return $sum;
    }
}
