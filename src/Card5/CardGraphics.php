<?php

namespace App\Card5;

use App\Card\Card;

class CardGraphics extends Card
{
    private $unicodeBase = [
        'Spades' => '1F0A',
        'Hearts' => '1F0B',
        'Diamonds' => '1F0C',
        'Clubs' => '1F0D'
    ];

    private $rankMap = [
        'Ace' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9',
        '10' => 'A',
        'Jack' => 'B',
        'Queen' => 'D',
        'King' => 'E'
    ];
    public function __construct(string $suit, string $rank)
    {
        parent::__construct($suit, $rank);
    }

    public function __toString()
    {
        $suitBase = $this->unicodeBase[$this->suit];
        $rankCode = $this->rankMap[$this->rank];
        $unicodeHex = $suitBase . $rankCode;
        $htmlEntity = "&#x" . $unicodeHex . ";";
        return $htmlEntity;
    }
}
