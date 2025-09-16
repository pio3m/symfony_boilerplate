<?php

class TaskService {
    private $repo;

    public function __construct() {
        $this->repo = new TaskRepository(); // klasa sama tworzy zależność
    }
}

class TaskService {
    public function __construct(private TaskRepository $repo) {}
}

namespace App\Service;

class Notifier
{
    public function __construct(
        private string $apiKey,
        private \Psr\Log\LoggerInterface $logger
    ) {}
}

