<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\PlayerManager;
use App\Card5\Pot;
use App\Card5\EventLogger;
use App\Card5\HandEvaluator;

class BettingRound
{
    private array $bets = [];
    private int $lastBet = -1;
    private PlayerManager $playerManager;
    private Pot $pot;
    private EventLogger $eventLogger;
    private HandEvaluator $handEvaluator;
    private int $ante;

    public function __construct(
        PlayerManager $playerManager,
        Pot $pot,
        EventLogger $eventLogger,
        HandEvaluator $handEvaluator,
        int $ante
    ) {
        $this->playerManager = $playerManager;
        $this->pot = $pot;
        $this->eventLogger = $eventLogger;
        $this->handEvaluator = $handEvaluator;
        $this->ante = $ante;
    }

    private function addBet(int $bet): void
    {
        $this->bets[] = $bet;
        $this->lastBet = $bet;
    }

    public function isBettingRoundOver(int $numberOfPlayers): bool
    {
        if ($this->lastBet === -1) {
            return false;
        }
        if (count($this->bets) !== $numberOfPlayers) {
            $players = $this->playerManager->getPlayers();
            // has the rest folded?
            $count = $numberOfPlayers - count($this->bets);
            if (count(array_filter($players, fn ($player) => $player->hasFolded())) === $count) {
                return true;
            }
            return false;
        }
        if (count(array_unique($this->bets)) !== 1) {
            return false;
        }
        return true;
    }

    public function getBets(): array
    {
        return $this->bets;
    }

    public function hasBets(): bool
    {
        return !empty($this->bets);
    }

    public function reset(): void
    {
        $this->bets = [];
        $this->lastBet = -1;
    }

    public function computerTurn(int $playerId): void
    {
        $this->eventLogger->log("Datorns tur att betta");
        $currentPlayer = $this->playerManager->getPlayers()[$playerId];
        $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
        $bet = $this->getBet($handStrength);
        if ($this->lastBet === -1) {
            if ($bet === 0) {
                $this->addBet(0);
                $this->eventLogger->log("Datorn checkar");
            } else {
                $this->pot->add($bet);
                $this->addBet($bet);
                $this->eventLogger->log("Datorn bettar " . $bet . " kr");
            }
        } elseif ($this->lastBet === 0) {
            if ($bet === 0) {
                $this->addBet($bet);
                $this->eventLogger->log("Datorn synar");
            } else {
                $this->pot->add($bet);
                $this->addBet($bet);
                $this->eventLogger->log("Datorn bettar " . $bet . " kr");
            }
        } else {
            if ($bet >= $this->lastBet) {
                $this->pot->add($this->lastBet);
                $this->addBet($this->lastBet);
                $this->eventLogger->log("Datorn synar");
            } else {
                $this->eventLogger->log("Datorn lägger sig");
                $this->playerManager->fold($playerId);
            }
        }
    }

    public function playerCheck(): void
    {
        $this->eventLogger->log("Spelaren checkar");
        $this->addBet(0);
    }

    public function playerBet(int $playerId): void
    {
        $currentPlayer = $this->playerManager->getPlayers()[$playerId];
        $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
        $bet = $this->getBet($handStrength);
        $bet = $bet === 0 ? $this->ante : $bet;
        $bet = $this->lastBet === -1 ? $bet : ($bet > $this->lastBet ? $bet : $this->lastBet + $this->ante);
        $this->pot->add($bet);
        $this->eventLogger->log("Spelaren bettar " . $bet . " kr");
        $this->addBet($bet);
    }

    public function playerCall(): void
    {
        $this->eventLogger->log("Spelaren synar");
        $this->pot->add($this->lastBet);
        $this->addBet($this->lastBet);
    }

    public function playerFold(int $playerId): void
    {
        $this->eventLogger->log("Spelaren lägger sig");
        $this->playerManager->fold($playerId);
    }

    private function getBet(string $handStrength): int
    {
        switch ($handStrength) {
            case "Straight Flush":
            case "Four of a Kind":
            case "Full House":
                return 30;
            case "Three of a Kind":
            case "Flush":
            case "Straight":
                return 20;
            case "Two Pair":
                return 15;
            default:
                return 0;
        }
    }
}
