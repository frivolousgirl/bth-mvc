<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\HandEvaluator;
use App\Card5\DeckOfCards;
use App\Card5\Pot;
use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\PlayerManager;

class Game
{
    private $events = [];
    private PlayerManager $playerManager;
    private DeckOfCards $deck;
    private Pot $pot;
    private GameState $gameState;
    private HandEvaluator $handEvaluator;
    private BettingRound $bettingRound;
    private int $ante = 10;
    private int $currentPlayer = 0;
    private int $startingPlayer = 0;
    private bool $gameOver = false;

    public function __construct(PlayerManager $playerManager
        , HandEvaluator $handEvaluator
        , DeckOfCards $deck
        , Pot $pot
        , GameState $gameState
        , BettingRound $bettingRound
        )
    {
        $this->playerManager = $playerManager;
        $this->handEvaluator = $handEvaluator;
        $this->deck = $deck;
        $this->pot = $pot;
        $this->gameState = $gameState;
        $this->bettingRound = $bettingRound;

        $this->reset();
    }

    public function getPot(): int
    {
        return $this->pot->getAmount();
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
            && !$this->bettingRound->hasBets();
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
            && count($this->bettingRound->getBets()) < count($this->getPlayers());
    }

    public function canCall(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && $this->bettingRound->hasBets();
    }

    public function canFold(): bool
    {
        $state = $this->getState();

        return $this->isPlayersTurn()
            && ($state === "FIRST_BETTING_ROUND" || $state === "SECOND_BETTING_ROUND")
            && $this->bettingRound->hasBets();
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
        $this->playerManager->reset();
        $this->pot->reset();
        $this->bettingRound->reset();
        $this->gameState->setState("ANTE");

        $this->gameOver = false;

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
        return $this->getPlayers()[$this->currentPlayer];
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
        return $this->playerManager->getPlayers();
    }

    public function getState(): string
    {
        return $this->gameState->getState();
    }

    private function ante(): void
    {
        $this->pot->add(count($this->getPlayers()) * $this->ante);
    }

    public function action(array $postData): void
    {
        $action = isset($postData["action"]) ? $postData["action"] : "";

        switch ($this->getState()) {
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
                    $numberOfPlayers = count($this->getPlayers());
                    if ($this->bettingRound->isBettingRoundOver($numberOfPlayers)) {
                        $this->bettingRound->reset();
                        if ($this->nextRound() === "SHOWDOWN") {
                            $this->decideWinner();
                            $this->showdown();
                        }
                    } else {
                        $folds = array_filter($this->getPlayers(), function (Player $player) {
                            return $player->hasFolded();
                        });

                        if (count($folds) > 0) {
                            $this->decideWinner();
                            $this->showdown();
                        }
                    }
                    break;
                }
            case "DRAW":
                {
                    $this->draw($postData);
                    if ($this->allSwapped()) {
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
        foreach ($this->getPlayers() as $player) {
            if ($player->hasFolded()) {
                $folds++;
            } elseif ($winner === null) {
                $winner = $player;
            } else {
                $winner = null;
                break;
            }
        }

        if ($folds === count($this->getPlayers()) - 1 && $winner !== null) {
            $this->addEvent("Spelet är slut");
            $this->addEvent("Alla spelare utom en lade sig");
            $this->addEvent($winner->name . " tar hem potten på " . $this->pot->getAmount() . " kr");
            return true;
        }

        return false;
    }

    private function decideWinner(): void
    {
        if ($this->allButOneFolded()) {
            return;
        }

        $winners = $this->handEvaluator->evaluateBestHand($this->getPlayers());
        $winnerCount = count($winners);

        if ($winnerCount === 0) {
            $this->addEvent("Ingen vinnare korad då inga spelare deltog. Märkligt...");
        } elseif ($winnerCount === 1) {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $this->addEvent("Spelet är slut");
            $this->addEvent("Vinnare är " . $winners[0]->name . " med följande hand: " . $hand);
            $this->addEvent($winners[0]->name . " tar hem potten på " . $this->pot->getAmount() . " kr");
        } else {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $money = $this->pot->getAmount() / $winnerCount;
            $this->addEvent("Spelet är slut");
            $this->addEvent("Det blev oavgjort. Alla spelare hade följande hand: " . $hand);
            $this->addEvent("Spelarna delar på potten vilket innebär " . $money . " kr vardera");
        }
    }

    private function allSwapped(): bool
    {
        foreach ($this->getPlayers() as $player) {
            if (!$player->hasSwapped()) {
                return false;
            }
        }

        return true;
    }

    private function draw(array $postData): void
    {
        $action = $postData["action"];
        $currentPlayer = $this->getCurrentPlayer();

        switch ($action) {
            case "computer_turn":
                {
                    $cards = $currentPlayer->decideCardsToSwap();
                    $this->addEvent("Datorn byter " . count($cards) . " kort");

                    $this->playerManager->discardAndDraw($this->currentPlayer
                        , $cards
                        , $this->deck
                    );
                    break;
                }
            case "stand_pat":
                {
                    // happy with current cards
                    $this->addEvent("Spelaren byter inga kort");
                    $this->playerManager->discardAndDraw($this->currentPlayer
                        , []
                        , $this->deck
                    );
                    break;
                }
            case "swap":
                {
                    $cards = explode(",", $postData["selectedCards"]);
                    $this->addEvent("Spelaren byter " . count($cards) . " kort");
                    $this->playerManager->discardAndDraw($this->currentPlayer
                        , $cards
                        , $this->deck
                    );
                    break;
                }
        }
        $this->nextPlayer();
    }

    private function lastBet(array $array): int
    {
        if (count($array) === 0) {
            return -1;
        }

        return end($array);
    }

    private function bettingRound(string $action): void
    {
        $currentPlayer = $this->getCurrentPlayer();

        switch ($action) {
            case "computer_turn":
                {
                    $this->addEvent("Datorns tur att betta");
                    $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
                    $bet = $this->getBet($handStrength);
                    $lastBet = $this->bettingRound->getLastBet();
                    if ($lastBet === -1) {
                        if ($bet === 0) {
                            $this->bettingRound->addBet(0);
                            $this->addEvent("Datorn checkar");
                        } else {
                            $this->pot->add($bet);
                            $this->bettingRound->addBet($bet);
                            $this->addEvent("Datorn bettar " . $bet . " kr");
                        }
                    } elseif ($lastBet === 0) {
                        if ($bet === 0) {
                            $this->bettingRound->addBet($bet);
                            $this->addEvent("Datorn synar");
                        } else {
                            $this->pot->add($bet);
                            $this->bettingRound->addBet($bet);
                            $this->addEvent("Datorn bettar " . $bet . " kr");
                        }
                    } else {
                        if ($bet >= $lastBet) {
                            $this->pot->add($lastBet);
                            $this->bettingRound->addBet($lastBet);
                            $this->addEvent("Datorn synar");
                        } elseif ($bet < $lastBet) {
                            $this->addEvent("Datorn lägger sig");
                            $this->playerManager->fold($this->currentPlayer);
                        }
                    }
                    $this->nextPlayer();
                    break;
                }
            case "check":
                {
                    $this->addEvent("Spelaren checkar");
                    $this->bettingRound->addBet(0);
                    $this->nextPlayer();
                    break;
                }
            case "bet":
                {
                    $handStrength = $this->handEvaluator->evaluateHand($currentPlayer->hand);
                    $lastBet = $this->bettingRound->getLastBet();
                    $bet = $this->getBet($handStrength);
                    $bet = $bet === 0 ? $this->ante : $bet;
                    $bet = $lastBet === -1 ? $bet : ($bet > $lastBet ? $bet : $lastBet + $this->ante);
                    $this->pot->add($bet);
                    $this->addEvent("Spelaren bettar " . $bet . " kr");
                    $this->bettingRound->addBet($bet);
                    $this->nextPlayer();
                    break;
                }
            case "call":
                {
                    $this->addEvent("Spelaren synar");
                    $lastBet = $this->bettingRound->getLastBet();
                    $this->pot->add($lastBet);
                    $this->bettingRound->addBet($lastBet);
                    $this->nextPlayer();
                    break;
                }
            case "fold":
                {
                    $this->addEvent("Spelaren lägger sig");
                    $this->playerManager->fold($this->currentPlayer);
                    $this->showdown();
                    break;
                }
        }
    }

    private function nextPlayer(): void
    {
        $this->currentPlayer++;

        if ($this->currentPlayer === count($this->getPlayers())) {
            $this->currentPlayer = 0;
        }
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

    public function nextRound(): string
    {
        return $this->gameState->nextState();
    }

    public function dealCards()
    {
        $this->playerManager->dealCards($this->deck);
    }

    public function showdown(): void
    {
        $this->gameState->setState("SHOWDOWN");
    }
}
