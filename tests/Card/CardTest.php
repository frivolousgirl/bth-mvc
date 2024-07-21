<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardTest extends TestCase
{
    public function testValueFromRank(): void
    {
        $this->assertEquals(2, Card::valueFromRank('2'));
        $this->assertEquals(3, Card::valueFromRank('3'));
        $this->assertEquals(4, Card::valueFromRank('4'));
        $this->assertEquals(5, Card::valueFromRank('5'));
        $this->assertEquals(6, Card::valueFromRank('6'));
        $this->assertEquals(7, Card::valueFromRank('7'));
        $this->assertEquals(8, Card::valueFromRank('8'));
        $this->assertEquals(9, Card::valueFromRank('9'));
        $this->assertEquals(10, Card::valueFromRank('10'));
        $this->assertEquals(11, Card::valueFromRank('Jack'));
        $this->assertEquals(12, Card::valueFromRank('Queen'));
        $this->assertEquals(13, Card::valueFromRank('King'));
        $this->assertEquals(14, Card::valueFromRank('Ace'));
        $this->assertEmpty(Card::valueFromRank('blaha'));
    }

    public function testGetSuitSymbolForHearts(): void
    {
        $card = new Card('Hearts', '1');

        $this->assertEquals("♥", $card->getSuitSymbol());
    }

    public function testGetSuitSymbolForSpades(): void
    {
        $card = new Card('Spades', '1');

        $this->assertEquals("♠", $card->getSuitSymbol());
    }

    public function testGetSuitSymbolForClubs(): void
    {
        $card = new Card('Clubs', '1');

        $this->assertEquals("♣", $card->getSuitSymbol());
    }

    public function testGetSuitSymbolForDiamonds(): void
    {
        $card = new Card('Diamonds', '1');

        $this->assertEquals("♦", $card->getSuitSymbol());
    }

    public function testGetSuitSymbolReturnsEmptyStringWithInvalidSuit(): void
    {
        $card = new Card('blaha', '1');

        $this->assertEmpty($card->getSuitSymbol());
    }

    public function testSuitAndRankAreAssignedCorrectly(): void
    {
        $card = new Card("Diamonds", "7");

        $this->assertEquals("Diamonds", $card->suit);
        $this->assertEquals("7", $card->rank);
    }

    public function testToStringReturnsBothSuitAndRank(): void
    {
        $card = new Card("Diamonds", "7");

        $this->assertEquals("♦ 7", $card->__toString());
    }
}
