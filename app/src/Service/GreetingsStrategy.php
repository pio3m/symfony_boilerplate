<?php
namespace App\Service;

interface GreetingsStrategy {
    public function greet(string $name): string;
}
