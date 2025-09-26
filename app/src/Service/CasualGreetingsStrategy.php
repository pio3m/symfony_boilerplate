<?php
namespace App\Service;
use App\Service\GreetingsStrategy;


class CasualGreetingsStrategy implements GreetingsStrategy
{
    public function greet(string $name): string
    {
        return "Hi " . $name . "!";
    }
}
