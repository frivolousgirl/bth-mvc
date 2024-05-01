<?php

namespace App\Dice;

class DiceGraphic extends Dice
{
    /** @var string[] */
    private $representation = [
        '⚀',
        '⚁',
        '⚂',
        '⚃',
        '⚄',
        '⚅',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function getAsString(): string
    {
        return "<span class='dice-graphic'>{$this->representation[$this->value - 1]}</span>";
    }
}
