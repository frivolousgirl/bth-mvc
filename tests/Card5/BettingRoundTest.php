<?php

use PHPUnit\Framework\TestCase;
use App\Card5\BettingRound;

class BettingRoundTest extends TestCase
{
    private BettingRound $bettingRound;

    protected function setUp(): void
    {
        $this->bettingRound = new BettingRound();
    }

    public function testAddBetUpdatesBetsAndLastBet(): void
    {
        $this->bettingRound->addBet(10);
        $this->assertSame([10], $this->bettingRound->getBets());
        $this->assertSame(10, $this->bettingRound->getLastBet());

        $this->bettingRound->addBet(20);
        $this->assertSame([10, 20], $this->bettingRound->getBets());
        $this->assertSame(20, $this->bettingRound->getLastBet());
    }

    public function testIsBettingRoundOverReturnsTrueWhenAllPlayersBetSame(): void
    {
        $numberOfPlayers = 3;

        $this->bettingRound->addBet(10);
        $this->bettingRound->addBet(10);
        $this->bettingRound->addBet(10);

        $this->assertTrue($this->bettingRound->isBettingRoundOver($numberOfPlayers));
    }

    public function testIsBettingRoundOverReturnsFalseWhenBetsAreNotEqual(): void
    {
        $numberOfPlayers = 3;

        $this->bettingRound->addBet(10);
        $this->bettingRound->addBet(20);
        $this->bettingRound->addBet(10);

        $this->assertFalse($this->bettingRound->isBettingRoundOver($numberOfPlayers));
    }

    public function testIsBettingRoundOverReturnsFalseWhenNotAllPlayersHaveBet(): void
    {
        $numberOfPlayers = 3;

        $this->bettingRound->addBet(10);
        $this->bettingRound->addBet(10);

        $this->assertFalse($this->bettingRound->isBettingRoundOver($numberOfPlayers));
    }

    public function testGetLastBetReturnsMinusOneWhenNoBets(): void
    {
        $this->assertSame(-1, $this->bettingRound->getLastBet());
    }

    public function testHasBetsReturnsTrueWhenBetsExist(): void
    {
        $this->assertFalse($this->bettingRound->hasBets());

        $this->bettingRound->addBet(10);

        $this->assertTrue($this->bettingRound->hasBets());
    }

    public function testResetClearsBetsAndLastBet(): void
    {
        $this->bettingRound->addBet(10);
        $this->bettingRound->addBet(20);

        $this->bettingRound->reset();

        $this->assertSame([], $this->bettingRound->getBets());
        $this->assertSame(-1, $this->bettingRound->getLastBet());
    }
}
