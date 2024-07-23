<?php

use App\Card5\GameState;
use PHPUnit\Framework\TestCase;

class GameStateTest extends TestCase
{
    private GameState $gameState;

    protected function setUp(): void
    {
        $this->gameState = new GameState();
    }

    public function testInitialStateIsAnte(): void
    {
        $this->assertEquals("ANTE", $this->gameState->getState());
    }

    public function testNextStateCyclesThroughStates(): void
    {
        $this->assertEquals("ANTE", $this->gameState->getState());
        
        $this->gameState->nextState();
        $this->assertEquals("DEALING", $this->gameState->getState());

        $this->gameState->nextState();
        $this->assertEquals("FIRST_BETTING_ROUND", $this->gameState->getState());

        $this->gameState->nextState();
        $this->assertEquals("DRAW", $this->gameState->getState());

        $this->gameState->nextState();
        $this->assertEquals("SECOND_BETTING_ROUND", $this->gameState->getState());

        $this->gameState->nextState();
        $this->assertEquals("SHOWDOWN", $this->gameState->getState());

        $this->gameState->nextState();
        $this->assertEquals("ANTE", $this->gameState->getState());
    }

    public function testSetStateSetsCorrectState(): void
    {
        $this->gameState->setState("DRAW");
        $this->assertEquals("DRAW", $this->gameState->getState());

        $this->gameState->setState("SHOWDOWN");
        $this->assertEquals("SHOWDOWN", $this->gameState->getState());
    }

    public function testSetStateInvalidStateDoesNothing(): void
    {
        $this->gameState->setState("INVALID_STATE");
        $this->assertEquals("ANTE", $this->gameState->getState());
    }
}
