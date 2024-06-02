<?php

namespace App\Card5;

use App\Card5\Player;

class Game 
{
    private $players = [];
    private $events = [];
    private DeckOfCards $deck;
    private int $pot = 0;
    private string $state;
    private int $ante = 10;
    private $states = [
        "ANTE",
        "DEALING",
        "FIRST_BETTING_ROUND",
        "DRAW",
        "SECOND_BETTING_ROUND",
        "SHOWDOWN"
    ];
    private int $currentState = 0;
    private $firstBettingRound = [];
    private $secondBettingRound = [];
    private int $currentPlayer = 0;

    public function __construct(array $playerNames)
    {   
        foreach ($playerNames as $name) {
            $this->players[] = new Player($name);
        }

        $this->deck = new DeckOfCards();

        $this->reset();
    }

    public function getPot(): int
    {
        return $this->pot;
    }

    private function isPlayersTurn(): bool
    {
        return $this->getCurrentPlayer()->name === "Jag";
    }

    public function canCheck(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && $state === "FIRST_BETTING_ROUND"
            && count($this->firstBettingRound) === 0;
    }

    public function canBet(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && $state === "FIRST_BETTING_ROUND"
            && count($this->players) - count($this->firstBettingRound) > 1;
    }

    public function canCall(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && $state === "FIRST_BETTING_ROUND"
            && count($this->firstBettingRound) > 0;
    }

    public function canFold(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && $state === "FIRST_BETTING_ROUND"
            && count($this->firstBettingRound) > 0;
    }

    public function reset(): void
    {
        $this->deck->reset();

        foreach ($this->players as $player)
        {
            $player->reset();
        }

        $this->pot = 0;
        $this->currentState = 0;

        $this->firstBettingRound = [];
        $this->secondBettingRound = [];
        $this->events = [];

        $this->currentPlayer = rand(0, 1) === 0;
    }

    public function getCurrentPlayer(): Player
    {
        return $this->players[$this->currentPlayer];
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addEvent(string $event): void
    {
        array_unshift($this->events, $event);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getState(): string
    {
        return $this->states[$this->currentState];
    }

    private function ante(): void
    {
        $this->pot += count($this->players) * $this->ante;
    }

    public function action(string $action): void
    {
        switch ($this->getState())
        {
            case "ANTE":
                $this->ante();
                $this->addEvent("Alla spelare har satsat");
                $this->nextRound();
                break;
            case "DEALING":
                $this->dealCards();
                $this->addEvent("Spelarna har fått 5 kort var");
                $this->nextRound();
                break;
            case "FIRST_BETTING_ROUND":
                $this->firstBettingRound($action);
                break;
        }
    }

    private function firstBettingRound(string $action): void
    {
        $currentPlayer = $this->getCurrentPlayer();

        switch ($action)
        {
            case "":
                $this->addEvent("Datorns tur att betta");
                array_push($this->firstBettingRound, $currentPlayer->name);
                $handStrength = $currentPlayer->evaluateHand();
                $bet = $this->getBet($handStrength);
                $this->pot += $bet;
                if ($bet > 0)
                {
                    $this->addEvent("Datorn bettar " . $bet . " kr");
                }
                else
                {
                    $this->addEvent("Dator checkar");
                }
                $this->nextPlayer();
                break;
        }
    }

    private function nextPlayer(): void
    {
        $this->currentPlayer++;

        if ($this->currentPlayer === count($this->players))
        {
            $this->currentPlayer = 0;
        }
    }

    private function getBet(string $handStrength): int
    {
        switch ($handStrength)
        {
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

    public function nextRound(): void
    {
       if ($this->currentState === count($this->states) - 1)
       {
            $this->currentState = 0;
       }
       else
       {
            $this->currentState += 1;
       }
    }

    public function dealCards() {
        foreach ($this->players as $player) {
            $player->receiveCards($this->deck->deal(5));
        }
    }

    public function bettingRound() {
        // Simplified betting logic
        foreach ($this->players as $player) {
            if (!$player->hasFolded()) {
                // In a real game, you'd prompt the player for their action (bet, call, raise, fold)
                //echo "{$player->name} bets.\n";
                $this->pot += 10;  // Simplified fixed bet for all players
            }
        }
    }

    public function drawRound() {
        foreach ($this->players as $player) {
            if (!$player->hasFolded()) {
                // In a real game, you'd prompt the player for which cards to discard
                // Here, we'll just simulate drawing new cards
                $player->discardAndDraw([0, 1], $this->deck); // Example: discarding first two cards
                //echo "{$player->name} draws new cards: " . $player->showHand() . "\n";
            }
        }
    }

    public function showdown() {
        // Simplified hand comparison (in reality, you'd need to implement hand ranking logic)
        $bestPlayer = null;
        foreach ($this->players as $player) {
            if (!$player->hasFolded()) {
                if ($bestPlayer === null || rand(0, 1) === 0) { // Randomly selecting a winner for simplicity
                    $bestPlayer = $player;
                }
            }
        }
        //echo "The winner is {$bestPlayer->name} with hand: " . $bestPlayer->showHand() . "\n";
        //echo "The pot is {$this->pot}\n";
    }
}
