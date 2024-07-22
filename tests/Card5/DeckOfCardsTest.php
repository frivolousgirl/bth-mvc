<?php

use PHPUnit\Framework\TestCase;
use App\Card5\DeckOfCards;
use App\Card5\CardGraphics;

class DeckOfCardsTest extends TestCase
{
    public function testDeckInitialization()
    {
        $deck = new DeckOfCards();
        $this->assertCount(52, $deck->deal(52));
    }

    public function testDeckReset()
    {
        $deck = new DeckOfCards();
        $deck->deal(5);
        $deck->reset();
        $this->assertCount(52, $deck->deal(52));
    }

    public function testDealOneCard()
    {
        $deck = new DeckOfCards();
        $dealtCards = $deck->deal(1);
        $this->assertCount(1, $dealtCards);
        $this->assertInstanceOf(CardGraphics::class, $dealtCards[0]);
    }

    public function testDealMultipleCards()
    {
        $deck = new DeckOfCards();
        $dealtCards = $deck->deal(5);
        $this->assertCount(5, $dealtCards);
        foreach ($dealtCards as $card) {
            $this->assertInstanceOf(CardGraphics::class, $card);
        }
    }

    public function testDeckSizeAfterDealing()
    {
        $deck = new DeckOfCards();
        $deck->reset();
        $deck->deal(5);
        $remainingCards = $deck->deal(47);
        $this->assertCount(47, $remainingCards);
    }

    public function testDealingMoreCardsThanInDeck()
    {
        $deck = new DeckOfCards();
        $deck->deal(52);
        $dealtCards = $deck->deal(1);
        $this->assertCount(0, $dealtCards);
    }
}
