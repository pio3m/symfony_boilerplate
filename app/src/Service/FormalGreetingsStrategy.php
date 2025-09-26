<?php
namespace App\Service;

use App\Service\GreetingsStrategy;

class FormalGreetingsStrategy implements GreetingsStrategy
{
    public function greet(string $name): string
    {
        return "Good day, " . $name . ".";
    }
}
