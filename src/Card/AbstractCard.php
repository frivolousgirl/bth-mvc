<?php

namespace App\Card;

abstract class AbstractCard
{
    public $suit; // The suit of the card (e.g., hearts, diamonds, clubs, spades)
    public $rank; // The rank of the card (e.g., 2, 3, 4, ..., Jack, Queen, King, Ace)

    protected function __construct($suit, $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function getSuitSymbol()
    {
        switch ($this->suit) {
            case 'Hearts':
                return '♥';
            case 'Diamonds':
                return '♦';
            case 'Clubs':
                return '♣';
            case 'Spades':
                return '♠';
            default:
                return '';
        }
    }

    public function __toString(): string
    {
        return "{$this->getSuitSymbol()} of {$this->rank}";
    }
}
