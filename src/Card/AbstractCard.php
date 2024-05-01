<?php

namespace App\Card;

abstract class AbstractCard
{
    /** @var string */
    public $suit; // The suit of the card (e.g., hearts, diamonds, clubs, spades)
    /** @var string */
    public $rank; // The rank of the card (e.g., 2, 3, 4, ..., Jack, Queen, King, Ace)

    protected function __construct(string $suit, string $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function getSuitSymbol(): string
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

    public function __toString()
    {
        return $this->getSuitSymbol() . ' ' . $this->rank;
    }
}
