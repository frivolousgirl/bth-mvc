<?php

namespace App\Card5;

use App\Card5\GameActionChecker;
use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\PlayerManager;
use PHPUnit\Framework\TestCase;

class GameActionCheckerTest extends TestCase
{
    public function testCanCheckWhenFirstBettingRoundNoBets()
    {
        $gameState = $this->createMock(GameState::class);
        $gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $bettingRound = $this->createMock(BettingRound::class);
        $bettingRound->expects($this->once())->method('hasBets')->willReturn(false);

        $playerManager = $this->createMock(PlayerManager::class);
        $playerManager->expects($this->once())->method('getPlayers')->willReturn([new Player('Jag', new HandEvaluator())]);

        $checker = new GameActionChecker($gameState, $bettingRound, $playerManager);

        $result = $checker->canCheck(0);

        $this->assertTrue($result);
    }

    public function testCanBetWhenFirstBettingRoundNoBets()
    {
        $gameState = $this->createMock(GameState::class);
        $gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $bettingRound = $this->createMock(BettingRound::class);
        $bettingRound->expects($this->once())->method('getBets')->willReturn([]);

        $playerManager = $this->createMock(PlayerManager::class);
        $playerManager->expects($this->exactly(2))->method('getPlayers')->willReturn([new Player('Jag', new HandEvaluator())]);

        $checker = new GameActionChecker($gameState, $bettingRound, $playerManager);

        $result = $checker->canBet(0);

        $this->assertTrue($result);
    }

    public function testCanDrawWhenInDrawState()
    {
        $gameState = $this->createMock(GameState::class);
        $gameState->expects($this->once())->method('getState')->willReturn('DRAW');

        $bettingRound = $this->createMock(BettingRound::class);

        $playerManager = $this->createMock(PlayerManager::class);
        $playerManager->expects($this->once())->method('getPlayers')->willReturn([new Player('Jag', new HandEvaluator())]);

        $checker = new GameActionChecker($gameState, $bettingRound, $playerManager);

        $result = $checker->canDraw(0);

        $this->assertTrue($result);
    }

    public function testCanCallWhenFirstBettingRoundWithBets()
    {
        $gameState = $this->createMock(GameState::class);
        $gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $bettingRound = $this->createMock(BettingRound::class);
        $bettingRound->expects($this->once())->method('hasBets')->willReturn(true);

        $playerManager = $this->createMock(PlayerManager::class);
        $playerManager->expects($this->exactly(1))->method('getPlayers')->willReturn([new Player('Jag', new HandEvaluator())]);

        $checker = new GameActionChecker($gameState, $bettingRound, $playerManager);

        $result = $checker->canCall(0);

        $this->assertTrue($result);
    }

    public function testCanFoldWhenFirstBettingRoundWithBets()
    {
        $gameState = $this->createMock(GameState::class);
        $gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $bettingRound = $this->createMock(BettingRound::class);
        $bettingRound->expects($this->once())->method('hasBets')->willReturn(true);

        $playerManager = $this->createMock(PlayerManager::class);
        $playerManager->expects($this->once())->method('getPlayers')->willReturn([new Player('Jag', new HandEvaluator())]);

        $checker = new GameActionChecker($gameState, $bettingRound, $playerManager);

        $result = $checker->canFold(0);

        $this->assertTrue($result);
    }
}
