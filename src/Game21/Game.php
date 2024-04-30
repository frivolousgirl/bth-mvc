<?php

namespace App\Game21;

use App\Card\DeckOfCards;
use App\Game21\Player;

class Game
{
    private Player $player;
    private Player $bank;
    private DeckOfCards $deck;
    private bool $canTakeCard;
    private bool $canStop;
    private bool $gameOver;

    public function __construct()
    {
        $this->player = new Player();
        $this->bank = new Player();
        $this->deck = new DeckOfCards();
    }

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

    public function getPlayerPoints(): int
    {
        return $this->player->sumCardValues();
    }

    public function getBankPoints(): int
    {
        return $this->bank->sumCardValues();
    }

    public function getPlayerCards(): array
    {
        return $this->player->getCards();
    }

    public function getBankCards(): array
    {
        return $this->bank->getCards();
    }

    public function getCanTakeCard(): bool
    {
        return $this->canTakeCard && !$this->gameOver;
    }

    public function getCanStop(): bool
    {
        return $this->canStop && !$this->gameOver;
    }

    public function drawPlayerCard(FlashMessage $flashMessage): void
    {
        $card = $this->deck->drawCard();
        $this->player->addCard($card);

        $this->canStop = true;

        if ($this->player->sumCardValues() >= 21)
        {
            $this->setGameOver($flashMessage);
        }
    }

    private function setGameOver(FlashMessage $flashMessage): void
    {
        $this->gameOver = true;

        $player1Sum = $this->player->sumCardValues();
        $player2Sum = $this->bank->sumCardValues();
        
        if ($player1Sum > 21)
        {
            $flashMessage->addFlashMessage('gameover', 'Game Over... Du Förlorade!');
        }
        else if ($player2Sum > 21)
        {
            $flashMessage->addFlashMessage('winning', 'Grattis, Du Vann!');
        }
        else if ($player2Sum >= $player1Sum)
        {
            $flashMessage->addFlashMessage('gameover', 'Game Over... Banken Vann Denna Gång!');
        }
        else
        {
            $flashMessage->addFlashMessage('winning', 'Grattis, du vann!');
        }
    }

    public function playerStays(FlashMessage $flashMessage): void
    {
        $this->canTakeCard = false;
        $this->canStop = false;

        while ($this->bank->sumCardValues() < $this->player->sumCardValues())
        {
            $card = $this->deck->drawCard();
            $this->bank->addCard($card);
        }

        $this->setGameOver($flashMessage);
    }
}
