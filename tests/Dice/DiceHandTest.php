<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class DiceHand.
 */
class DiceHandTest extends TestCase
{
    public function testCanAddDie()
    {
        $hand = new DiceHand();

        $this->assertEquals(0, $hand->getNumberDices());

        $hand->add(new Dice());

        $this->assertEquals(1, $hand->getNumberDices());
    }

    public function testRollRollsAllDice()
    {
        $hand = new DiceHand();

        $hand->add(new Dice());
        $hand->add(new Dice());

        foreach ($hand->getValues() as $value)
        {
            $this->assertEquals(0, $value);
        }

        $hand->roll();

        foreach ($hand->getValues() as $value)
        {
            $this->assertGreaterThanOrEqual(1, $value);
            $this->assertLessThanOrEqual(6, $value);
        }
    }

    public function testGetStringReturnsNonEmptyStrings()
    {
        $hand = new DiceHand();

        $hand->add(new Dice());
        $hand->add(new Dice());

        $hand->roll();

        foreach ($hand->getString() as $value)
        {
            $this->assertNotEmpty($value);
        }
    }

    public function testToStringReturnsNonEmptyStrings()
    {
        $hand = new DiceHand();

        $hand->add(new Dice());
        $hand->add(new Dice());

        $hand->roll();

        $this->assertNotEmpty($hand->__toString());
    }
}
