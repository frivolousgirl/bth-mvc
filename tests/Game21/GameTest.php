<?php

namespace App\Game21;

use App\Game21\Player;
use App\Game21\Game;
use App\Card\DeckOfCards;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;
    private Player $player;
    private Player $bank;
    private DeckOfCards $deck;
    private FlashMessage $flashMessage;

    protected function setUp(): void
    {
        $this->flashMessage = $this->createMock(FlashMessage::class);

        $this->player = new Player();
        $this->bank = new Player();
        $this->deck = new DeckOfCards();
        $this->game = new Game($this->player, $this->bank, $this->deck);
    }

    public function testCanGetPlayerPoints()
    {
        $this->assertEquals(0, $this->player->sumCardValues());

        $this->game->drawPlayerCard($this->flashMessage);
        $this->game->drawPlayerCard($this->flashMessage);

        $this->assertEquals($this->player->sumCardValues(), $this->game->getPlayerPoints());
    }

    public function testCanGetBankPoints()
    {
        $this->assertEquals(0, $this->bank->sumCardValues());

        $this->game->drawPlayerCard($this->flashMessage);
        $this->game->playerStays($this->flashMessage);

        $this->assertNotEquals(0, $this->bank->sumCardValues());
        $this->assertEquals($this->bank->sumCardValues(), $this->game->getBankPoints());
    }

    public function testWhenPlayerHasDrawnACardTheyCanStop()
    {
        $this->assertFalse($this->game->getCanStop());

        $this->game->drawPlayerCard($this->flashMessage);

        $this->assertTrue($this->game->getCanStop());
    }

    public function testWhenGameStartsPlayerCanTakeCard()
    {
        $this->assertTrue($this->game->getCanTakeCard());
    }

    public function testCanGetPlayerCards()
    {
        $this->assertCount(0, $this->game->getPlayerCards());

        $this->game->drawPlayerCard($this->flashMessage);
        $this->game->drawPlayerCard($this->flashMessage);

        $cards = $this->game->getPlayerCards();

        $this->assertCount(2, $cards);
        $this->assertEquals($cards, $this->player->getCards());
    }

    public function testCanGetBankCards()
    {
        $this->assertCount(0, $this->game->getBankCards());

        $this->game->drawPlayerCard($this->flashMessage);
        $this->game->playerStays($this->flashMessage);

        $cards = $this->game->getBankCards();

        $this->assertNotCount(0, $cards);
        $this->assertEquals($cards, $this->bank->getCards());
    }

    public function testWhenPlayerStaysTheyCantTakeAnotherCard()
    {
        $this->game->playerStays($this->flashMessage);

        $this->assertFalse($this->game->getCanTakeCard());
    }

    public function testWhenPlayerStaysTheyNoLongerCanChooseToStop()
    {
        $this->game->playerStays($this->flashMessage);

        $this->assertFalse($this->game->getCanStop());
    }
}