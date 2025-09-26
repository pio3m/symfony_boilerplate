<?php
namespace App\Service;
use App\Service\CasualGreetingsStrategy;

class GreetingsResolver
{
    public static function byKey(string $key): GreetingsStrategy
    {

        return match ($key) {
            'formal' => new FormalGreetingsStrategy(),
            'casual' => new CasualGreetingsStrategy(),
            default => throw new \Exception('Unknown strategy'),
        };
    }
}
