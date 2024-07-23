<?php

use App\Card5\Pot;
use PHPUnit\Framework\TestCase;

class PotTest extends TestCase
{
    private Pot $pot;

    protected function setUp(): void
    {
        $this->pot = new Pot();
    }

    public function testInitialAmountIsZero(): void
    {
        $this->assertEquals(0, $this->pot->getAmount());
    }

    public function testAddIncreasesAmount(): void
    {
        $this->pot->add(100);
        $this->assertEquals(100, $this->pot->getAmount());
        
        $this->pot->add(50);
        $this->assertEquals(150, $this->pot->getAmount());
    }

    public function testResetResetsAmountToZero(): void
    {
        $this->pot->add(100);
        $this->assertEquals(100, $this->pot->getAmount());
        
        $this->pot->reset();
        $this->assertEquals(0, $this->pot->getAmount());
    }
}
