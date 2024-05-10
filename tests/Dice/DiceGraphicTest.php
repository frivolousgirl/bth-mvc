<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class DiceGraphic.
 */
class DiceGraphicTest extends TestCase
{
    public function testGetAsStringReturnsNullWhenDiceNotRolled(): void
    {
        $dice = new DiceGraphic();

        $this->assertEmpty($dice->getAsString());
    }

    public function testGetAsStringReturnsNonEmptyString(): void
    {
        $dice = new DiceGraphic();

        $dice->roll();

        $this->assertNotEmpty($dice->getAsString());
    }
}
