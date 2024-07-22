<?php

use PHPUnit\Framework\TestCase;
use App\Card5\HandEvaluator;
use App\Card5\CardGraphics;
use App\Card5\Player;

class HandEvaluatorTest extends TestCase
{
    public function testEvaluateHandForStraightFlush()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('hearts', 'Jack'),
            new CardGraphics('hearts', 'Queen'),
            new CardGraphics('hearts', 'King'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('Straight Flush', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForFourOfAKind()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', '10'),
            new CardGraphics('spades', '10'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('Four of a Kind', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForFullHouse()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', '10'),
            new CardGraphics('spades', 'Ace'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('Full House', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForFlush()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '2'),
            new CardGraphics('hearts', '5'),
            new CardGraphics('hearts', '9'),
            new CardGraphics('hearts', 'Jack'),
            new CardGraphics('hearts', 'King')
        ];

        $this->assertEquals('Flush', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForStraight()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('clubs', 'Jack'),
            new CardGraphics('hearts', 'Queen'),
            new CardGraphics('spades', 'King'),
            new CardGraphics('diamonds', 'Ace')
        ];

        $this->assertEquals('Straight', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForThreeOfAKind()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', '10'),
            new CardGraphics('spades', 'King'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('Three of a Kind', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForTwoPair()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', 'King'),
            new CardGraphics('spades', 'King'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('Two Pair', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForOnePair()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', 'King'),
            new CardGraphics('spades', 'Queen'),
            new CardGraphics('hearts', 'Ace')
        ];

        $this->assertEquals('One Pair', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateHandForHighCard()
    {
        $handEvaluator = new HandEvaluator();

        $hand = [
            new CardGraphics('hearts', '2'),
            new CardGraphics('diamonds', '5'),
            new CardGraphics('clubs', '9'),
            new CardGraphics('spades', 'Jack'),
            new CardGraphics('hearts', 'King')
        ];
        $this->assertEquals('High Card', $handEvaluator->evaluateHand($hand));
    }

    public function testEvaluateBestHand()
    {
        $handEvaluator = new HandEvaluator();

        $player1 = new Player('Player 1', $handEvaluator);

        $player1->receiveCards([
            new CardGraphics('hearts', '10'),
            new CardGraphics('diamonds', '10'),
            new CardGraphics('clubs', 'King'),
            new CardGraphics('spades', 'Queen'),
            new CardGraphics('hearts', 'Ace')
        ]);

        $player2 = new Player('Player 2', $handEvaluator);

        $player2->receiveCards([
            new CardGraphics('hearts', '2'),
            new CardGraphics('hearts', '5'),
            new CardGraphics('hearts', '9'),
            new CardGraphics('hearts', 'Jack'),
            new CardGraphics('hearts', 'King')
        ]);

        $player3 = new Player('Player 3', $handEvaluator);

        $player3->receiveCards([
            new CardGraphics('hearts', '10'),
            new CardGraphics('hearts', 'Jack'),
            new CardGraphics('hearts', 'Queen'),
            new CardGraphics('hearts', 'King'),
            new CardGraphics('hearts', 'Ace')
        ]);

        $players = [$player1, $player2, $player3];

        $winners = $handEvaluator->evaluateBestHand($players);

        $this->assertCount(1, $winners);
        $this->assertEquals('Player 3', $winners[0]->name);
    }
}
