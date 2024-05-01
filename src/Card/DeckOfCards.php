<?php

namespace App\Card;

class DeckOfCards
{
    /** @var Card[] */
    private $cards = [];

    // Constructor
    public function __construct()
    {
        $this->initializeDeck();
    }

    // Initialize the deck of cards
    private function initializeDeck(): void
    {
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace'];

        $this->cards = array();

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }
    }

    // Shuffle the deck of cards
    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    // Draw a card from the deck
    public function drawCard(): ?Card
    {
        if (empty($this->cards)) {
            return null; // Return null if the deck is empty
        }
        return array_shift($this->cards); // Remove and return the top card from the deck
    }

    // Get the number of cards remaining in the deck
    public function countCards(): int
    {
        return count($this->cards);
    }

    // Reset the deck of cards (reinitialize)
    public function reset(): void
    {
        $this->initializeDeck();
    }

    /** @return Card[] */
    public function getAllCardsSorted(): array
    {
        $cards = $this->cards;

        $compareFunction = function ($card1, $card2) {
            $suitComparison = strcmp($card1->suit, $card2->suit);

            if ($suitComparison !== 0) {
                return $suitComparison;
            }

            return $card1->rank <=> $card2->rank;
        };

        usort($cards, $compareFunction);

        return $cards;
    }

    /** @return Card[] */
    public function getAllCards(): array
    {
        return $this->cards;
    }

    public function __toString(): string
    {
        // Convert the deck to a string representation
        $cardsAsString = '';
        foreach ($this->cards as $card) {
            $cardsAsString .= $card . "<br>"; // Assuming Card class has a __toString() method
        }
        return $cardsAsString;
    }
}
