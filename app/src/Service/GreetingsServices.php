<?php

namespace App\Service;

class GreetingsService
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getGreeting(): string
    {
        return $this->message;
    }
}