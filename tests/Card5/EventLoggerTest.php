<?php

use PHPUnit\Framework\TestCase;
use App\Card5\EventLogger;

class EventLoggerTest extends TestCase
{
    private EventLogger $eventLogger;

    protected function setUp(): void
    {
        $this->eventLogger = new EventLogger();
    }

    public function testLogAddsEventToTheEventsArray(): void
    {
        $event1 = "Event 1";
        $event2 = "Event 2";

        $this->eventLogger->log($event1);
        $this->assertSame([$event1], $this->eventLogger->getEvents());

        $this->eventLogger->log($event2);
        $this->assertSame([$event2, $event1], $this->eventLogger->getEvents());
    }

    public function testGetEventsReturnsEventsInCorrectOrder(): void
    {
        $event1 = "Event 1";
        $event2 = "Event 2";

        $this->eventLogger->log($event1);
        $this->eventLogger->log($event2);

        $events = $this->eventLogger->getEvents();
        $this->assertCount(2, $events);
        $this->assertSame($event2, $events[0]);
        $this->assertSame($event1, $events[1]);
    }

    public function testClearEmptiesTheEventsArray(): void
    {
        $event1 = "Event 1";
        $event2 = "Event 2";

        $this->eventLogger->log($event1);
        $this->eventLogger->log($event2);

        $this->eventLogger->clear();
        $this->assertSame([], $this->eventLogger->getEvents());
    }
}
