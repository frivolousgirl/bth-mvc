<?php

namespace App\Card5;

use App\Card5\CardGraphics;

class DeckOfCards
{
    private $cards = [];

    public function reset(): void
    {
        $this->cards = [];

        $suits = ['Spades', 'Diamonds', 'Clubs', 'Hearts'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace'];
        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this->cards[] = new CardGraphics($suit, $rank);
            }
        }

        shuffle($this->cards);
    }

    public function __construct()
    {
        $this->reset();
    }

    public function deal(int $count = 1): array
    {
        return array_splice($this->cards, 0, $count);
    }
}
