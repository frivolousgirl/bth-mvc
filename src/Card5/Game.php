<?php

namespace App\Card5;

use App\Card5\Player;
use App\Card5\HandEvaluator;
use App\Card5\DeckOfCards;
use App\Card5\Pot;
use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\PlayerManager;
use App\Card5\EventLogger;
use App\Card5\GameActionChecker;
use App\Card5\RandomNumberGenerator;

class Game
{
    private EventLogger $eventLogger;
    private PlayerManager $playerManager;
    private DeckOfCards $deck;
    private Pot $pot;
    private GameState $gameState;
    private HandEvaluator $handEvaluator;
    private BettingRound $bettingRound;
    private GameActionChecker $gameActionChecker;
    private RandomNumberGenerator $randomNumberGenerator;
    private int $currentPlayer = 0;
    private int $startingPlayer = 0;

    const ANTE = 10;

    public function __construct(PlayerManager $playerManager
        , HandEvaluator $handEvaluator
        , DeckOfCards $deck
        , Pot $pot
        , GameState $gameState
        , BettingRound $bettingRound
        , EventLogger $eventLogger
        , GameActionChecker $gameActionChecker
        , RandomNumberGenerator $randomNumberGenerator
        )
    {
        $this->playerManager = $playerManager;
        $this->handEvaluator = $handEvaluator;
        $this->deck = $deck;
        $this->pot = $pot;
        $this->gameState = $gameState;
        $this->bettingRound = $bettingRound;
        $this->eventLogger = $eventLogger;
        $this->gameActionChecker = $gameActionChecker;
        $this->randomNumberGenerator = $randomNumberGenerator;

        $this->reset();
    }

    public function getPot(): int
    {
        return $this->pot->getAmount();
    }

    public function canCheck(): bool
    {
        return $this->gameActionChecker->canCheck($this->currentPlayer);
    }

    public function canBet(): bool
    {
        return $this->gameActionChecker->canBet($this->currentPlayer);
    }

    public function canCall(): bool
    {
        return $this->gameActionChecker->canCall($this->currentPlayer);
    }

    public function canFold(): bool
    {
        return $this->gameActionChecker->canFold($this->currentPlayer);
    }

    public function canDraw(): bool
    {
        return $this->gameActionChecker->canDraw($this->currentPlayer);
    }

    public function reset(): void
    {
        $this->deck->reset();
        $this->playerManager->reset();
        $this->pot->reset();
        $this->bettingRound->reset();
        $this->gameState->setState("ANTE");

        $this->eventLogger->clear();

        $numberOfPlayers = count($this->playerManager->getPlayers());

        $this->currentPlayer = $this->randomNumberGenerator->generate(0, $numberOfPlayers - 1);
        $this->startingPlayer = $this->currentPlayer;
    }

    public function getEvents(): array
    {
        return $this->eventLogger->getEvents();
    }

    public function getCurrentPlayer(): Player
    {
        return $this->getPlayers()[$this->currentPlayer];
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
        $this->pot->add(count($this->getPlayers()) * self::ANTE);
    }

    public function action(array $postData): void
    {
        $action = isset($postData["action"]) ? $postData["action"] : "";

        switch ($this->getState()) {
            case "ANTE":
                $this->handleAnte();
                break;
            case "DEALING":
                $this->handleDealing();
                break;
            case "FIRST_BETTING_ROUND":
            case "SECOND_BETTING_ROUND":
                $this->handleBettingRound($action);
                break;
            case "DRAW":
                $this->handleDraw($postData);
                break;
        }
    }

    private function handleAnte(): void
    {
        $this->ante();
        $this->eventLogger->log("Alla spelare har satsat");
        $this->nextRound();
    }

    private function handleDealing(): void
    {
        $this->dealCards();
        $this->eventLogger->log("Spelarna har fått 5 kort var");
        $this->nextRound();
    }

    private function handleBettingRound(string $action): void
    {
        switch ($action) {
            case "computer_turn":
                $this->bettingRound->computerTurn($this->currentPlayer);
                break;
            case "check":
                $this->bettingRound->playerCheck();
                break;
            case "bet":
                $this->bettingRound->playerBet($this->currentPlayer);
                break;
            case "call":
                $this->bettingRound->playerCall();
                break;
            case "fold":
                $this->bettingRound->playerFold($this->currentPlayer);
                break;
        }
        $this->nextPlayer();

        if ($this->allButOneFolded()) {
            $this->decideWinner(true);
            $this->showdown();
        }
        elseif ($this->isBettingRoundOver()) {
            $this->endBettingRound();
        }
    }

    private function isBettingRoundOver(): bool
    {
        $numberOfPlayers = count($this->getPlayers());
        return $this->bettingRound->isBettingRoundOver($numberOfPlayers);
    }

    private function endBettingRound(): void
    {
        $this->bettingRound->reset();
        if ($this->nextRound() === "SHOWDOWN") {
            $this->decideWinner(false);
            $this->showdown();
        }
    }

    private function handleDraw(array $postData): void
    {
        $this->draw($postData);
        if ($this->allSwapped()) {
            $this->currentPlayer = $this->startingPlayer;
            $this->nextRound();
        }
    }

    private function allButOneFolded(): bool
    {
        $players = $this->getPlayers();

        return count(array_filter($players, fn ($player) => $player->hasFolded()))
            === count($players) - 1;
    }

    private function decideWinner(bool $byFolding): void
    {
        if ($byFolding) {
            $this->handleWinnerByFolding();
            return;
        }

        $winners = $this->handEvaluator->evaluateBestHand($this->getPlayers());
        $this->handleWinners($winners);
    }

    private function handleWinnerByFolding(): void
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
            $this->eventLogger->log("Spelet är slut");
            $this->eventLogger->log("Alla spelare utom en lade sig");
            $this->eventLogger->log($winner->name . " tar hem potten på " . $this->pot->getAmount() . " kr");
        }
    }

    private function handleWinners(array $winners): void
    {
        $winnerCount = count($winners);

        if ($winnerCount === 0) {
            $this->eventLogger->log("Ingen vinnare korad då inga spelare deltog. Märkligt...");
        } elseif ($winnerCount === 1) {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $this->eventLogger->log("Spelet är slut");
            $this->eventLogger->log("Vinnare är " . $winners[0]->name . " med följande hand: " . $hand);
            $this->eventLogger->log($winners[0]->name . " tar hem potten på " . $this->pot->getAmount() . " kr");
        } else {
            $hand = $this->handEvaluator->evaluateHand($winners[0]->hand);
            $money = $this->pot->getAmount() / $winnerCount;
            $this->eventLogger->log("Spelet är slut");
            $this->eventLogger->log("Det blev oavgjort. Alla spelare hade följande hand: " . $hand);
            $this->eventLogger->log("Spelarna delar på potten vilket innebär " . $money . " kr vardera");
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
        $currentPlayer = $this->getCurrentPlayer();

        $cardsToDiscard = $this->getCardsToDiscard($postData, $currentPlayer);
        $this->playerManager->discardAndDraw($this->currentPlayer, $cardsToDiscard, $this->deck);

        $this->nextPlayer();
    }

    private function getCardsToDiscard(array $postData, Player $currentPlayer): array
    {
        $action = $postData["action"] ?? "";

        switch ($action) {
            case "computer_turn":
                $cardsToDiscard = $currentPlayer->decideCardsToSwap();
                $this->eventLogger->log("Datorn byter " . count($cardsToDiscard) . " kort");
                break;
            case "stand_pat":
                $cardsToDiscard = [];
                $this->eventLogger->log("Spelaren byter inga kort");
                break;
            case "swap":
                $cardsToDiscard = explode(",", $postData["selectedCards"]);
                $this->eventLogger->log("Spelaren byter " . count($cardsToDiscard) . " kort");
                break;
        }

        return $cardsToDiscard;
    }

    private function lastBet(array $array): int
    {
        if (count($array) === 0) {
            return -1;
        }

        return end($array);
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
