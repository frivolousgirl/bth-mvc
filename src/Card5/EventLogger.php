<?php

namespace App\Card5;

class EventLogger
{
    private array $events = [];

    public function log(string $event): void
    {
        array_unshift($this->events, $event);
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }
}
