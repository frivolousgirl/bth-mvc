<?php

namespace App\Card;

use App\Card\AbstractCard;

class Card extends AbstractCard
{
    // Constructor
    public function __construct($suit, $rank)
    {
        parent::__construct($suit, $rank);
    }

    public static function valueFromRank($rank) {
        switch ($rank) {
            case '2':
                return 2;
            case '3':
                return 3;
            case '4':
                return 4;
            case '5':
                return 5;
            case '6':
                return 6;
            case '7':
                return 7;
            case '8':
                return 8;
            case '9':
                return 9;
            case '10':
                return 10;
            case 'Jack':
                return 11;
            case 'Queen':
                return 12;
            case 'King':
                return 13;
            case 'Ace':
                return 1;
            default:
                return null;
        }
    }
}
