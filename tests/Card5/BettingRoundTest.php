<?php

namespace App\Tests\Card5;

use PHPUnit\Framework\TestCase;
use App\Card5\Player;
use App\Card5\PlayerManager;
use App\Card5\Pot;
use App\Card5\EventLogger;
use App\Card5\HandEvaluator;
use App\Card5\BettingRound;

class BettingRoundTest extends TestCase
{
    private $playerManager;
    private $pot;
    private $eventLogger;
    private $handEvaluator;
    private $bettingRound;
    private $player1;
    private $player2;

    protected function setUp(): void
    {
        $this->playerManager = $this->createMock(PlayerManager::class);
        $this->pot = $this->createMock(Pot::class);
        $this->eventLogger = $this->createMock(EventLogger::class);
        $this->handEvaluator = $this->createMock(HandEvaluator::class);

        $this->bettingRound = new BettingRound($this->playerManager, $this->pot, $this->eventLogger, $this->handEvaluator, 5);

        $this->player1 = $this->createMock(Player::class);
        $this->player2 = $this->createMock(Player::class);

        $this->player1->hand = [];
        $this->player2->hand = [];

        $this->playerManager->method('getPlayers')->willReturn([$this->player1, $this->player2]);
    }

    public function testComputerTurnNoPreviousBetsCheck()
    {
        $this->handEvaluator->method('evaluateHand')->willReturn('High Card');

        $this->eventLogger->expects($this->exactly(2))
            ->method('log')
            ->withConsecutive(
                ['Datorns tur att betta'],
                ['Datorn checkar']
            );

        $this->bettingRound->computerTurn(1);
        $this->assertSame([0], $this->bettingRound->getBets());
    }

    public function testComputerTurnNoPreviousBetsBetNonZero()
    {
        $this->handEvaluator->method('evaluateHand')->willReturn('Three of a Kind');

        $this->eventLogger->expects($this->exactly(2))
            ->method('log')
            ->withConsecutive(
                ['Datorns tur att betta'],
                ['Datorn bettar 20 kr']
            );

        $this->pot->expects($this->once())->method('add')->with(20);

        $this->bettingRound->computerTurn(1);
        $this->assertSame([20], $this->bettingRound->getBets());
    }

    public function testComputerTurnPreviousBetIsZeroBetNonZero()
    {
        $this->handEvaluator->method('evaluateHand')->willReturn('Flush');

        $this->eventLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                ['Spelaren checkar'],
                ['Datorns tur att betta'],
                ['Datorn bettar 20 kr']
            );

        $this->pot->expects($this->once())->method('add')->with(20);

        $this->bettingRound->playerCheck();
        $this->bettingRound->computerTurn(1);

        $this->assertSame([0, 20], $this->bettingRound->getBets());
    }

    public function testComputerTurnWhenComputerHasWeakerHand()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturnOnConsecutiveCalls(
                'Full House',
                'One Pair'
            );

        $this->eventLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                ['Spelaren bettar 30 kr'],
                ['Datorns tur att betta'],
                ['Datorn lägger sig']
            );

        $this->pot->expects($this->exactly(1))
            ->method('add')
            ->with($this->equalTo(30));

        $this->bettingRound->playerBet(0);
        $this->bettingRound->computerTurn(1);

        $this->assertSame([30], $this->bettingRound->getBets());
    }

    public function testComputerCallsMatchingPlayerBetWhenHandIsStronger()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturnOnConsecutiveCalls(
                'Two Pair',
                'Full House'
            );

        $this->eventLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                ['Spelaren bettar 15 kr'],
                ['Datorns tur att betta'],
                ['Datorn synar']
            );

        $this->pot->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [15],
                [15]
            );

        $this->bettingRound->playerBet(0);
        $this->bettingRound->computerTurn(1);

        $this->assertSame([15, 15], $this->bettingRound->getBets());
    }

    public function testIsBettingRoundOverNoBets()
    {
        $this->assertFalse($this->bettingRound->isBettingRoundOver(2));
    }

    public function testIsBettingRoundOverPlayerBetsComputerChecks()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturnOnConsecutiveCalls('Three of a Kind', 'High Card');

        $this->bettingRound->playerBet(0);
        $this->bettingRound->computerTurn(1);

        $this->assertFalse($this->bettingRound->isBettingRoundOver(2));
    }

    public function testIsBettingRoundOverPlayerBetsComputerCalls()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturnOnConsecutiveCalls('Two Pair', 'Two Pair');

        $this->bettingRound->playerBet(0);
        $this->bettingRound->computerTurn(1);

        $this->assertTrue($this->bettingRound->isBettingRoundOver(2));
    }

    public function testIsBettingRoundOverPlayerBetsComputerFolds()
    {
        $this->player1->method('hasFolded')->willReturn(false);
        $this->player2->method('hasFolded')->willReturn(true);

        $this->handEvaluator->method('evaluateHand')
            ->willReturnOnConsecutiveCalls('Two Pair', 'High Card');

        $this->bettingRound->playerBet(0); // Player bets
        $this->bettingRound->computerTurn(1);

        $this->assertTrue($this->bettingRound->isBettingRoundOver(2));
    }

    public function testIsBettingRoundOverPlayerChecksComputerBets()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturn('Flush');

        $this->bettingRound->playerCheck();
        $this->bettingRound->computerTurn(1);

        $this->assertFalse($this->bettingRound->isBettingRoundOver(2));
    }

    public function testHasBetsNoBets()
    {
        $this->assertFalse($this->bettingRound->hasBets());
    }

    public function testHasBetsWithBets()
    {
        $this->bettingRound->playerBet(0);
        $this->assertTrue($this->bettingRound->hasBets());
    }

    public function testResetAfterBets()
    {
        $this->bettingRound->playerBet(0);
        $this->assertTrue($this->bettingRound->hasBets());

        $this->bettingRound->reset();

        $this->assertFalse($this->bettingRound->hasBets());
        $this->assertSame([], $this->bettingRound->getBets());
    }

    public function testResetWithoutBets()
    {
        $this->bettingRound->reset();

        $this->assertFalse($this->bettingRound->hasBets());
        $this->assertSame([], $this->bettingRound->getBets());
    }

    public function testPlayerCall()
    {
        $this->handEvaluator->method('evaluateHand')
            ->willReturn('Flush');

        $this->pot->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [20],
                [20]
            );

        $this->eventLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                ['Datorns tur att betta'],
                ['Datorn bettar 20 kr'],
                ['Spelaren synar']
            );

        $this->bettingRound->computerTurn(1);
        $this->bettingRound->playerCall();

        $this->assertSame([20, 20], $this->bettingRound->getBets());
    }

    public function testPlayerFoldAfterBetting()
    {
        $this->eventLogger->expects($this->once())
            ->method('log')
            ->with('Spelaren lägger sig');

        $this->playerManager->expects($this->exactly(1))
            ->method('fold')
            ->with($this->equalTo(30));

        $this->bettingRound->playerFold(30);
    }
}
