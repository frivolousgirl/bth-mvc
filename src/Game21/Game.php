<?php

namespace App\Game21;

use App\Card\Card;
use App\Card\DeckOfCards;
use App\Game21\Player;

/**
 * Class representing a game of 21.
 */
class Game
{
    private Player $player;
    private Player $bank;
    private DeckOfCards $deck;
    private bool $canTakeCard;
    private bool $canStop;
    private bool $gameOver;

    /**
     * Constructor for the Game class.
     *
     * @param Player      $player The player object.
     * @param Player      $bank   The bank object.
     * @param DeckOfCards $deck   The deck of cards object.
     */
    public function __construct(Player $player, Player $bank, DeckOfCards $deck)
    {
        $this->player = $player;
        $this->bank = $bank;
        $this->deck = $deck;

        $this->init();
    }

    /**
     * Initializes the game.
     */
    public function init(): void
    {
        $this->player->init();
        $this->bank->init();

        $this->deck->reset();
        $this->deck->shuffle();

        $this->canTakeCard = true;
        $this->canStop = false;
        $this->gameOver = false;
    }

    /**
     * Get the total points of the player.
     *
     * @return int The total points of the player.
     */
    public function getPlayerPoints(): int
    {
        return $this->player->sumCardValues();
    }

    /**
     * Get the total points of the bank.
     *
     * @return int The total points of the bank.
     */
    public function getBankPoints(): int
    {
        return $this->bank->sumCardValues();
    }

    /**
     * Get an array of cards held by the player.
     *
     * @return array<Card> An array of cards held by the player.
     */
    public function getPlayerCards(): array
    {
        return $this->player->getCards();
    }

    /**
     * Get an array of cards held by the bank.
     *
     * @return array<Card> An array of cards held by the bank.
     */
    public function getBankCards(): array
    {
        return $this->bank->getCards();
    }

    /**
     * Check if the player can take another card.
     *
     * @return bool True if the player can take another card, false otherwise.
     */
    public function getCanTakeCard(): bool
    {
        return $this->canTakeCard && !$this->gameOver;
    }

    /**
     * Checks if the player can stop drawing cards.
     *
     * @return bool True if the player can stop, false otherwise.
     */
    public function getCanStop(): bool
    {
        return $this->canStop && !$this->gameOver;
    }


    /**
     * Draws a card for the player.
     *
     * @param FlashMessage $flashMessage The flash message object.
     */
    public function drawPlayerCard(FlashMessage $flashMessage): void
    {
        $card = $this->deck->drawCard();

        if ($card) {
            $this->player->addCard($card);
        }

        $this->canStop = true;

        if ($this->player->sumCardValues() >= 21) {
            $this->setGameOver($flashMessage);
        }
    }

    /**
    * Sets the game-over state and display appropriate messages.
    *
    * @param FlashMessage $flashMessage The flash message object.
    */
    private function setGameOver(FlashMessage $flashMessage): void
    {
        $this->gameOver = true;

        $player1Sum = $this->player->sumCardValues();
        $player2Sum = $this->bank->sumCardValues();

        if ($player1Sum > 21) {
            $flashMessage->addFlashMessage('gameover', 'Game Over... Du Förlorade!');
        } elseif ($player2Sum > 21 || $player2Sum < $player1Sum) {
            $flashMessage->addFlashMessage('winning', 'Grattis, Du Vann!');
        } else {
            $flashMessage->addFlashMessage('gameover', 'Game Over... Banken Vann Denna Gång!');
        }
    }

    /**
     * Lets the player stay in the game.
     *
     * @param FlashMessage $flashMessage The flash message object.
     */
    public function playerStays(FlashMessage $flashMessage): void
    {
        $this->canTakeCard = false;
        $this->canStop = false;

        while ($this->bank->sumCardValues() < $this->player->sumCardValues()) {
            $card = $this->deck->drawCard();

            if (!$card) {
                break;
            }
            $this->bank->addCard($card);
        }

        $this->setGameOver($flashMessage);
    }
}
