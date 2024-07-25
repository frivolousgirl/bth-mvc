<?php

namespace App\Card5;

use App\Card5\Game;
use App\Card5\PlayerManager;
use App\Card5\HandEvaluator;
use App\Card5\DeckOfCards;
use App\Card5\Pot;
use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\EventLogger;
use App\Card5\GameActionChecker;
use App\Card5\RandomNumberGenerator;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;
    private PlayerManager $playerManager;
    private HandEvaluator $handEvaluator;
    private DeckOfCards $deck;
    private Pot $pot;
    private GameState $gameState;
    private BettingRound $bettingRound;
    private EventLogger $eventLogger;
    private GameActionChecker $gameActionChecker;
    private RandomNumberGenerator $randomNumberGenerator;
    private Player $player1;
    private Player $player2;

    protected function setUp(): void
    {
        $this->playerManager = $this->createMock(PlayerManager::class);
        $this->handEvaluator = $this->createMock(HandEvaluator::class);
        $this->deck = $this->createMock(DeckOfCards::class);
        $this->pot = $this->createMock(Pot::class);
        $this->gameState = $this->createMock(GameState::class);
        $this->bettingRound = $this->createMock(BettingRound::class);
        $this->eventLogger = $this->createMock(EventLogger::class);
        $this->gameActionChecker = $this->createMock(GameActionChecker::class);
        $this->randomNumberGenerator = $this->createMock(RandomNumberGenerator::class);
        
        $this->randomNumberGenerator->expects($this->any())->method('generate')->willReturn(0);

        $this->player1 = new Player('Jag', $this->handEvaluator);
        $this->player2 = new Player('Datorn', $this->handEvaluator);

        $this->playerManager->expects($this->any())->method('getPlayers')->willReturn([$this->player1, $this->player2]);

        $this->game = new Game($this->playerManager,
            $this->handEvaluator,
            $this->deck,
            $this->pot,
            $this->gameState,
            $this->bettingRound,
            $this->eventLogger,
            $this->gameActionChecker,
            $this->randomNumberGenerator
        );
    }

    public function testHandleAnte()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('ANTE');
        $this->gameState->expects($this->once())->method('nextState')->willReturn('DEALING');
        $this->pot->expects($this->once())->method('add')->with(20);
        $this->eventLogger->expects($this->once())->method('log')->with('Alla spelare har satsat');

        $this->game->action(['action' => '']);
    }

    public function testHandleDealing()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('DEALING');

        $this->playerManager->expects($this->once())->method('dealCards')->with($this->deck);
        $this->eventLogger->expects($this->once())->method('log')->with('Spelarna har fått 5 kort var');
        $this->gameState->expects($this->once())->method('nextState')->willReturn('FIRST_BETTING_ROUND');

        $this->game->action(['action' => '']);
    }

    public function testBasicBettingRound()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('computerTurn')->with(0);
        $this->bettingRound->expects($this->once())->method('isBettingRoundOver')->willReturn(true);
        $this->bettingRound->expects($this->once())->method('reset');

        $this->gameState->expects($this->once())->method('nextState')->willReturn('DRAW');

        $this->game->action(['action' => 'computer_turn']);
    }

    public function testBettingRoundWithMultipleActions()
    {
        $this->gameState->expects($this->any())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('computerTurn')->with(0);
        $this->bettingRound->expects($this->once())->method('playerCheck')->with();
        $this->bettingRound->expects($this->any())->method('isBettingRoundOver')->willReturn(true);
        $this->bettingRound->expects($this->any())->method('reset');

        $this->gameState->expects($this->any())->method('nextState')->willReturn('DRAW');

        $this->game->action(['action' => 'computer_turn']);
        $this->game->action(['action' => 'check']);
    }

    public function testHandleCheckAction()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('playerCheck')->with();

        $this->game->action(['action' => 'check']);
    }

    public function testHandleBetAction()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('playerBet')->with(0);

        $this->game->action(['action' => 'bet']);
    }

    public function testHandleCallAction()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('playerCall')->with();

        $this->game->action(['action' => 'call']);
    }

    public function testHandleFoldAction()
    {
        $this->gameState->expects($this->once())->method('getState')->willReturn('FIRST_BETTING_ROUND');

        $this->bettingRound->expects($this->once())->method('playerFold')->with(0);

        $this->game->action(['action' => 'fold']);
    }
}
