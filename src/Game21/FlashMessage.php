<?php

namespace App\Game21;

interface FlashMessage
{
    public function addFlashMessage(string $type, mixed $message): void;
}