<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LuckyControllerTest extends WebTestCase
{
    public function testNumber()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lucky');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();

        preg_match('/numbers:\s([\d,\s]+)\sLucky\sStars:\s([\d,\s]+)/', $content, $matches);

        $numbers = explode(', ', $matches[1]);
        $starnumbers = explode(', ', $matches[2]);

        $this->assertCount(5, $numbers);
        $this->assertEquals(5, count(array_unique($numbers)));
        foreach ($numbers as $number) {
            $this->assertGreaterThanOrEqual(1, $number);
            $this->assertLessThanOrEqual(50, $number);
        }

        $this->assertCount(2, $starnumbers);
        $this->assertEquals(2, count(array_unique($starnumbers)));
        foreach ($starnumbers as $starnumber) {
            $this->assertGreaterThanOrEqual(1, $starnumber);
            $this->assertLessThanOrEqual(10, $starnumber);
        }
    }
}

