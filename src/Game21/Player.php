<?php

namespace App\Game21;

use App\Card\Card;

/**
 * Class representing a player in the game of 21.
 */
class Player
{
    /** @var Card[] An array holding the player's cards. */
    private $cards = [];

    /**
     * Constructor for the Player class.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
    * Add a card to the player's hand.
    *
    * @param Card $card The card to add to the player's hand.
    */
    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Get the number of cards in the player's hand.
     *
     * @return int The number of cards in the player's hand.
     */
    public function countCards(): int
    {
        return count($this->cards);
    }

    /**
     * Get an array of cards held by the player.
     *
     * @return array<Card> An array of cards held by the player.
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
    * Initialize the player's hand by removing all cards.
    */
    public function init(): void
    {
        $this->cards = [];
    }

    /**
     * Calculate the total value of the player's cards.
     *
     * @return int The total value of the player's cards.
     */
    public function sumCardValues(): int
    {
        $sum = 0;
        foreach ($this->cards as $card) {
            $sum += Card::valueFromRank($card->rank);
        }
        return $sum;
    }
}
