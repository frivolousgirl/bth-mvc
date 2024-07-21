<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\HandEvaluator;

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
    private $bettingRound = [];
    private $secondBettingRound = [];
    private int $currentPlayer = 0;
    private int $startingPlayer = 0;
    private HandEvaluator $handEvaluator;
    private bool $gameOver = false;

    public function __construct(array $playerNames)
    {   
        foreach ($playerNames as $name) {
            $this->players[] = new Player($name);
        }

        $this->handEvaluator = new HandEvaluator();

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
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && count($this->bettingRound) === 0;
    }

    private static function all(array $array, int $value): bool
    {
        return array_unique($array) === array($value);
    }

    public function canBet(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && count($this->bettingRound) < count($this->players);
    }

    public function canCall(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && count($this->bettingRound) > 0;
    }

    public function canFold(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && count($this->bettingRound) > 0;
    }

    public function canDraw(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && $state === "DRAW";
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

        $this->gameOver = false;

        $this->bettingRound = [];
        $this->secondBettingRound = [];
        $this->events = [];

        $this->currentPlayer = rand(0, 1);
        $this->startingPlayer = $this->currentPlayer;
    }

    public function isGameOver(): bool
    {
        return $this->gameOver;
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

    public function action(array $postData): void
    {
        $action = isset($postData["action"]) ? $postData["action"] : "";

        switch ($this->getState())
        {
            case "ANTE":
            {
                $this->ante();
                $this->addEvent("Alla spelare har satsat");
                $this->nextRound();
                break;
            }
            case "DEALING":
            {
                $this->dealCards();
                $this->addEvent("Spelarna har fått 5 kort var");
                $this->nextRound();
                break;
            }
            case "FIRST_BETTING_ROUND":
            case "SECOND_BETTING_ROUND":
            {
                $this->bettingRound($action);
                if (count(array_unique($this->bettingRound)) === 1 &&
                    count($this->bettingRound) === count($this->players) &&
                    $this->bettingRound[0] !== -1)
                {
                    $this->bettingRound = [];
                    if ($this->nextRound() === "SHOWDOWN")
                    {
                        $this->decideWinner();
                        $this->showdown();
                    }
                }
                else
                {
                    $folds = array_filter($this->players, function(Player $player){
                        return $player->hasFolded();
                    });

                    if (count($folds) > 0)
                    {
                        $this->decideWinner();
                        $this->showdown();
                    }
                }
                break;
            }
            case "DRAW":
            {
                $this->draw($postData);
                if ($this->allSwapped())
                {
                    $this->currentPlayer = $this->startingPlayer;
                    $this->nextRound();
                }
                break;
            }
        }
    }

    private function allButOneFolded(): bool
    {
        $winner = null;
        $folds = 0;
        foreach ($this->players as $player)
        {
            if ($player->hasFolded())
            {
                $folds++;
            }
            else if ($winner === null)
            {
                $winner = $player;
            }
            else
            {
                $winner = null;
                break;
            }
        }

        if ($folds === count($this->players) - 1 && $winner !== null)
        {
            $this->addEvent("Spelet är slut");
            $this->addEvent("Alla spelare utom en lade sig");
            $this->addEvent($winner->name . " tar hem potten på " . $this->pot . " kr");
            return true;
        }

        return false;
    }

    private function decideWinner(): void
    {
        if ($this->allButOneFolded())
        {
            return;
        }

        $winners = $this->handEvaluator->evaluateBestHand($this->players);
        $winnerCount = count($winners);

        if ($winnerCount === 0)
        {
            $this->addEvent("Ingen vinnare korad då inga spelare deltog. Märkligt...");
        }
        else if ($winnerCount === 1)
        {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $this->addEvent("Spelet är slut");
            $this->addEvent("Vinnare är " . $winners[0]->name . " med följande hand: " . $hand);
            $this->addEvent($winners[0]->name . " tar hem potten på " . $this->pot . " kr");
        }
        else
        {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $money = $this->pot / $winnerCount;
            $this->addEvent("Spelet är slut");
            $this->addEvent("Det blev oavgjort. Alla spelare hade följande hand: " . $hand);
            $this->addEvent("Spelarna delar på potten vilket innebär " . $money . " kr vardera");
        }    
    }

    private function allSwapped(): bool
    {
        foreach ($this->players as $player)
        {
            if (!$player->hasSwapped())
            {
                return false;
            }
        }

        return true;
    }

    private function draw(array $postData): void
    {
        $action = $postData["action"];
        $currentPlayer = $this->getCurrentPlayer();

        switch ($action)
        {
            case "computer_turn":
            {
                $cards = $currentPlayer->decideCardsToSwap();
                $this->addEvent("Datorn byter " . count($cards) . " kort");
                $currentPlayer->discardAndDraw($cards, $this->deck);
                break;
            }
            case "stand_pat":
            {
                // happy with current cards
                $this->addEvent("Spelaren byter inga kort");
                $currentPlayer->discardAndDraw([], $this->deck);
                break;
            }
            case "swap":
            {
                $cards = explode(",", $postData["selectedCards"]);
                $this->addEvent("Spelaren byter " . count($cards) . " kort");
                $currentPlayer->discardAndDraw($cards, $this->deck);
                break;
            }
        }
        $this->nextPlayer();
    }

    private function lastBet(array $array): int
    {
        if (count($array) === 0)
        {
            return -1;
        }

        return end($array);
    }

    private function bettingRound(string $action): void
    {
        $currentPlayer = $this->getCurrentPlayer();

        switch ($action)
        {
            case "computer_turn":
            {
                $this->addEvent("Datorns tur att betta");
                $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
                $bet = $this->getBet($handStrength);
                $lastBet = $this->lastBet($this->bettingRound);
                if ($lastBet === -1)
                {
                    if ($bet === 0)
                    {
                        array_push($this->bettingRound, 0);
                        $this->addEvent("Datorn checkar");
                    }
                    else
                    {
                        $this->pot += $bet;
                        array_push($this->bettingRound, $bet);
                        $this->addEvent("Datorn bettar " . $bet . " kr");
                    }
                }
                else if ($lastBet === 0)
                {
                    if ($bet === 0)
                    {
                        array_push($this->bettingRound, $bet);
                        $this->addEvent("Datorn synar");
                    }
                    else
                    {
                        $this->pot += $bet;
                        array_push($this->bettingRound, $bet);
                        $this->addEvent("Datorn bettar " . $bet . " kr");
                    }
                }
                else
                {
                    if ($bet >= $lastBet)
                    {
                        $this->pot += $lastBet;
                        array_push($this->bettingRound, $lastBet);
                        $this->addEvent("Datorn synar");
                    }
                    else if ($bet < $lastBet)
                    {
                        $this->addEvent("Datorn lägger sig");
                        $currentPlayer->fold();
                    }
                }
                $this->nextPlayer();
                break;
            }
            case "check":
            {
                $this->addEvent("Spelaren checkar");
                array_push($this->bettingRound, 0);
                $this->nextPlayer();
                break;
            }
            case "bet":
            {
                $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
                $lastBet = $this->lastBet($this->bettingRound);
                $bet = $this->getBet($handStrength);
                $bet = $bet === 0 ? $this->ante : $bet;
                $bet = $lastBet === -1 ? $bet : ($bet > $lastBet ? $bet : $lastBet + $this->ante);
                $this->pot += $bet;
                $this->addEvent("Spelaren bettar " . $bet . " kr");
                array_push($this->bettingRound, $bet);
                $this->nextPlayer();
                break;
            }
            case "call":
            {
                $this->addEvent("Spelaren synar");
                $lastBet = $this->lastBet($this->bettingRound);
                $this->pot += $lastBet;
                array_push($this->bettingRound, $lastBet);
                $this->nextPlayer();
                break;
            }
            case "fold":
            {
                $this->addEvent("Spelaren lägger sig");
                $currentPlayer->fold();
                $this->showdown();
                break;
            }
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

    public function nextRound(): string
    {
       if ($this->currentState === count($this->states) - 1)
       {
            $this->currentState = 0;
       }
       else
       {
            $this->currentState += 1;
       }

       return $this->states[$this->currentState];
    }

    public function dealCards() {
        foreach ($this->players as $player) {
            $player->receiveCards($this->deck->deal(5));
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

    public function showdown(): void
    {
        $this->currentState = array_search("SHOWDOWN", $this->states);
    }
}
