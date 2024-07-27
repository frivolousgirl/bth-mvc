<?php

namespace App\Card5;

use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\PlayerManager;

class GameActionChecker
{
    private GameState $gameState;
    private BettingRound $bettingRound;
    private PlayerManager $playerManager;

    public function __construct(
        GameState $gameState,
        BettingRound $bettingRound,
        PlayerManager $playerManager
    ) {
        $this->gameState = $gameState;
        $this->bettingRound = $bettingRound;
        $this->playerManager = $playerManager;
    }

    public function canCheck(int $playerId): bool
    {
        $state = $this->gameState->getState();

        return $this->isPlayersTurn($playerId)
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && !$this->bettingRound->hasBets();
    }

    public function canDraw(int $playerId): bool
    {
        $state = $this->gameState->getState();

        return $this->isPlayersTurn($playerId)
            && $state === "DRAW";
    }

    public function canBet(int $playerId): bool
    {
        $state = $this->gameState->getState();

        return $this->isPlayersTurn($playerId)
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && count($this->bettingRound->getBets()) < count($this->playerManager->getPlayers());
    }

    public function canCall(int $playerId): bool
    {
        $state = $this->gameState->getState();

        return $this->isPlayersTurn($playerId)
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && $this->bettingRound->hasBets();
    }

    public function canFold(int $playerId): bool
    {
        $state = $this->gameState->getState();

        return $this->isPlayersTurn($playerId)
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && $this->bettingRound->hasBets();
    }

    private function isPlayersTurn(int $playerId): bool
    {
        return $this->playerManager->getPlayers()[$playerId]->name === "Jag";
    }
}
