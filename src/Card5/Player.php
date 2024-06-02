<?php

namespace App\Card5;

use App\Card5\DeckOfCards;
use App\Card5\CardGraphics;
use App\Card\Card;

class Player {
    public $hand = [];
    public string $name;
    private $folded = false;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function reset(): void
    {
        $this->folded = false;
        $this->hand = [];
    }

    public function receiveCards(array $cards) {
        $this->hand = array_merge($this->hand, $cards);
    }

    public function discardAndDraw(array $indices, DeckOfCards $deck) {
        foreach ($indices as $index) {
            unset($this->hand[$index]);
        }
        $this->hand = array_values($this->hand);
        $newCards = $deck->deal(count($indices));
        $this->receiveCards($newCards);
    }

    public function fold() {
        $this->folded = true;
    }

    public function hasFolded() {
        return $this->folded;
    }

    public function evaluateHand(): string
    {
        $ranks = array_map(function(CardGraphics $card){
            return Card::valueFromRank($card->rank);
        }, $this->hand);

        $suits = array_map(function(CardGraphics $card){
            return $card->suit;
        }, $this->hand);

        sort($ranks);

        $isFlush = count(array_unique($suits)) === 1;
        $isStraight = $this->isStraight($ranks);

        if ($isFlush && $isStraight)
        {
            return "Straight Flush";
        }

        $rankCounts = array_count_values($ranks);
        $values = array_values($rankCounts);

        rsort($values);

        var_dump($values);

        if ($values[0] === 4)
        {
            return 'Four of a Kind';
        }

        if ($values[0] === 3 && $values[1] === 2)
        {
            return 'Full House';
        }

        if ($isFlush)
        {
            return 'Flush';
        } 

        if ($isStraight)
        {
            return 'Straight';
        }

        if ($values[0] === 3)
        {
            return 'Three of a Kind';
        }

        if ($values[0] === 2 && $values[1] === 2)
        {
            return 'Two Pair';
        }

        if ($values[0] === 2)
        {
            return 'One Pair';
        }

        return "High Card";
    }

    private function isStraight(array $ranks) {
        for ($i = 0; $i < count($ranks) - 1; $i++) {
            if ($ranks[$i] + 1 !== $ranks[$i + 1]) {
                return false;
            }
        }
        return true;
    }
}
