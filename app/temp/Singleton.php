<?php

final class Logger
{
    private static ?Logger $instance = null;

    private function __construct() {} // prywatny konstruktor

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log(string $message): void
    {
        echo "[LOG] " . $message . PHP_EOL;
    }
}

// --- użycie ---
$logger1 = Logger::getInstance();
$logger2 = Logger::getInstance();

$logger1->log("Hello World!");

var_dump($logger1 === $logger2); // true (to ten sam obiekt)
