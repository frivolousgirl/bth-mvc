<?php

namespace App\Game21;

use App\Card\Card;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    public function testCanAddCards(): void
    {
        $player = new Player();

        $this->assertCount(0, $player->getCards());

        $card = new Card("", "");

        $player->addCard($card);

        $this->assertCount(1, $player->getCards());
        $this->assertEquals($card, $player->getCards()[0]);
    }

    public function testCanCountCards(): void
    {
        $player = new Player();

        $this->assertEquals(0, $player->countCards());

        $player->addCard(new Card("", ""));

        $this->assertEquals(1, $player->countCards());
    }

    public function testCanSumCardValues(): void
    {
        $player = new Player();

        $this->assertEquals(0, $player->sumCardValues());

        $player->addCard(new Card("", "2"));
        $player->addCard(new Card("", "3"));
        $player->addCard(new Card("", "Jack"));

        $this->assertEquals(16, $player->sumCardValues());
    }
}
