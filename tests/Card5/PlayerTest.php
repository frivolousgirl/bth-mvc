<?php

use PHPUnit\Framework\TestCase;
use App\Card5\Player;
use App\Card5\DeckOfCards;
use App\Card5\HandEvaluator;
use App\Card5\CardGraphics;
use App\Card\Card;

class PlayerTest extends TestCase
{
    private HandEvaluator $handEvaluator;

    protected function setUp(): void
    {
        $this->handEvaluator = new HandEvaluator();
    }

    public function testReset()
    {
        $player = new Player('Player 1', $this->handEvaluator);
        $player->fold();
        $player->receiveCards([
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10')
        ]);
        $player->discardAndDraw([0, 1], $this->createMock(DeckOfCards::class));

        $player->reset();

        $this->assertFalse($player->hasFolded());
        $this->assertFalse($player->hasSwapped());
        $this->assertEmpty($player->hand);
    }

    public function testHasSwapped()
    {
        $player = new Player('Player 1', $this->handEvaluator);
        $this->assertFalse($player->hasSwapped());

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('deal')->willReturn([
            new CardGraphics('clubs', 'King'),
            new CardGraphics('spades', 'Queen')
        ]);

        $player->receiveCards([
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10')
        ]);

        $player->discardAndDraw([0, 1], $deck);

        $this->assertTrue($player->hasSwapped());
    }

    public function testReceiveCards()
    {
        $player = new Player('Player 1', $this->handEvaluator);
        $cards = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10')
        ];

        $player->receiveCards($cards);
        $this->assertCount(2, $player->hand);
        $this->assertEquals($cards, $player->hand);
    }

    public function testDiscardAndDraw()
    {
        $player = new Player('Player 1', $this->handEvaluator);

        $initialCards = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10')
        ];

        $player->receiveCards($initialCards);

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('deal')->willReturn([
            new CardGraphics('clubs', 'King'),
            new CardGraphics('spades', 'Queen')
        ]);

        $player->discardAndDraw([0, 1], $deck);

        $this->assertCount(2, $player->hand);
        $this->assertEquals('clubs', $player->hand[0]->suit);
        $this->assertEquals('King', $player->hand[0]->rank);
        $this->assertEquals('spades', $player->hand[1]->suit);
        $this->assertEquals('Queen', $player->hand[1]->rank);
    }

    public function testFold()
    {
        $player = new Player('Player 1', $this->handEvaluator);
        $this->assertFalse($player->hasFolded());

        $player->fold();
        $this->assertTrue($player->hasFolded());
    }

    public function testDecideCardsToSwap()
    {
        $handEvaluator = $this->createMock(HandEvaluator::class);

        // Mocking the HandEvaluator for testing purposes
        $handEvaluator->method('evaluateHand')->willReturn('High Card');

        $player = new Player('Player 1', $handEvaluator);

        $player->receiveCards([
            new CardGraphics('hearts', '2'),
            new CardGraphics('diamonds', '3'),
            new CardGraphics('clubs', '4'),
            new CardGraphics('spades', '5'),
            new CardGraphics('hearts', '6')
        ]);

        $swapIndices = $player->decideCardsToSwap();
        $this->assertNotEmpty($swapIndices);
    }
}
