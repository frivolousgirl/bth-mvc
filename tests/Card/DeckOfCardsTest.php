<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class DeckOfCardsTest.
 */
class DeckOfCardsTest extends TestCase
{
    public function testCreatingNewDeckInitializesCards()
    {
        $deck = new DeckOfCards();

        $this->assertEquals(52, $deck->countCards());
    }

    public function testShuffleShufflesCards()
    {
        $deck = new DeckOfCards();
        $originalDeck = $deck->getAllCards();

        $deck->shuffle();
        $shuffledDeck = $deck->getAllCards();

        $this->assertNotEquals($originalDeck, $shuffledDeck);
    }

    public function testDrawCardsReturnsNullWhenNoCards()
    {
        $deck = new DeckOfCards();

        while ($deck->countCards() > 0)
        {
            $deck->drawCard();
        }

        $this->assertEmpty($deck->drawCard());
    }

    public function testDrawCardReturnsTopCardInDeck()
    {
        $deck = new DeckOfCards();

        $deck->shuffle();

        $cards = $deck->getAllCards();

        $this->assertEquals($cards[0], $deck->drawCard());
    }

    public function testResetResetsCardsInDeck()
    {
        $deck = new DeckOfCards();

        $originalDeck = $deck->getAllCards();

        $deck->shuffle();
        $deck->reset();

        $resetDeck = $deck->getAllCards();

        $this->assertEquals($originalDeck, $resetDeck);
    }

    public function testToStringReturnsNonEmptyString()
    {
        $deck = new DeckOfCards();

        $this->assertNotEmpty($deck->__toString());
    }

    public function testGetAllCardsSortedReturnsCardsSorted()
    {
        $deck = new DeckOfCards();
        
        $sortedCards = $deck->getAllCardsSorted();
        
        $this->assertCount(52, $sortedCards);
        
        $this->assertTrue($this->isSorted($sortedCards));
    }

    // Helper function to check if an array of cards is sorted
    private function isSorted(array $cards): bool
    {
        $previousCard = null;
        foreach ($cards as $card) {
            if ($previousCard !== null && $this->compareCards($previousCard, $card) > 0) {
                return false; // Not sorted
            }
            $previousCard = $card;
        }
        return true; // Sorted
    }

    // Helper function to compare two cards
    private function compareCards(Card $card1, Card $card2): int
    {
        // Compare suits first
        $suitComparison = strcmp($card1->suit, $card2->suit);
        if ($suitComparison !== 0) {
            return $suitComparison;
        }
        
        // If suits are equal, compare ranks
        return $card1->rank <=> $card2->rank;
    }
}
