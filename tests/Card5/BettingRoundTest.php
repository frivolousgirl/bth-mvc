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

    protected function setUp(): void
    {
        $this->playerManager = $this->createMock(PlayerManager::class);
        $this->pot = $this->createMock(Pot::class);
        $this->eventLogger = $this->createMock(EventLogger::class);
        $this->handEvaluator = $this->createMock(HandEvaluator::class);

        $this->bettingRound = new BettingRound($this->playerManager, $this->pot, $this->eventLogger, $this->handEvaluator, 5);

        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        $player1->hand = [];
        $player2->hand = [];

        $this->playerManager->method('getPlayers')->willReturn([$player1, $player2]);
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
}
