<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Dice.
 */
class DiceTest extends TestCase
{
    /**
     * Construct object and verify that the object has the expected
     * properties, use no arguments.
     */
    public function testCreateDice()
    {
        $dice = new Dice();
        $this->assertInstanceOf("\App\Dice\Dice", $dice);

        $res = $dice->getAsString();
        $this->assertNotEmpty($res);
    }

    public function testRollReturnsRandomInteger()
    {
        $dice = new Dice();
        $value = $dice->roll();

        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(6, $value);
    }

    public function testGetValueReturnsRolledNumber()
    {
        $dice = new Dice();
        $value = $dice->roll();

        $this->assertEquals($value, $dice->getValue());
    }
}
