<?php

namespace App\Card5;

use App\Card5\CardGraphics;
use App\Card5\Player;
use App\Card\Card;

class HandEvaluator
{
    private const HAND_RANKS = [
        "Straight Flush" => 8,
        "Four of a Kind" => 7,
        "Full House" => 6,
        "Flush" => 5,
        "Straight" => 4,
        "Three of a Kind" => 3,
        "Two Pair" => 2,
        "One Pair" => 1,
        "High Card" => 0
    ];

    public function evaluateHand(array $hand): string
    {
        $ranks = array_map(function(CardGraphics $card){
            return Card::valueFromRank($card->rank);
        }, $hand);

        $suits = array_map(function(CardGraphics $card){
            return $card->suit;
        }, $hand);

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

    // Returns the players that have the best hands.
    public function evaluateBestHand(array $players): array
    {
        if (count($players) === 0)
        {
            return [];
        }

        $bestHandType = $this->evaluateHand($players[0]->hand);

        $winners = [$players[0]];

        for ($i = 1; $i < count($players); $i++)
        {
            $handType = $this->evaluateHand($players[$i]->hand);
            
            $bestHandRank = self::HAND_RANKS[$bestHandType];
            $currentHandRank = self::HAND_RANKS[$handType];

            if ($currentHandRank > $bestHandRank)
            {
                $bestHandRank = $currentHandRank;
                $winners = [$players[$i]];
            }
            else if ($currentHandRank === $bestHandRank)
            {
                array_push($winners, $players[$i]);
            }
        }

        return $winners;
    }
}