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
}
