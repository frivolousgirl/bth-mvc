<?php

namespace App\Card;

use App\Card\AbstractCard;

class Card extends AbstractCard
{
    // Constructor
    public function __construct(string $suit, string $rank)
    {
        parent::__construct($suit, $rank);
    }

    public static function valueFromRank(string $rank): ?int
    {
        $rankValues = [
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
                'Jack' => 11,
                'Queen' => 12,
                'King' => 13,
                'Ace' => 14,
            ];

        return $rankValues[$rank] ?? null;
    }
}
