<?php
namespace App\Service;

interface NotifierInterface
{
    public function getKey(): string;
    public function notify(string $message): void;
}
