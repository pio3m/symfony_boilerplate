<?php

namespace App\Temp;


class TaskRepository {

}   


class TaskService {

    private TaskRepository $repo;

    public function __construct(TaskRepository $repo) {
        $this->repo = $repo;
    }

    public function createTask(string $name): void {
        // logic to create a task
        // e.g., $this->repo->save($task);
    }
}


interface Logger {
    public function log(string $message);
}

class FileLogger implements Logger {

    public function __construct(private string $filePath) {}

    public function log(string $message) {
        echo "[File] " . $message;
    }
}

class DatabaseLogger implements Logger {
    public function log(string $message) {
        echo "[DB] " . $message;
    }
}

class UserService {
    private Logger $logger;

    // ✅ wstrzyknięcie zależności przez konstruktor
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function register(string $name) {
        $this->logger->log("Zarejestrowano użytkownika: $name");
    }

    //logowanie uzytkownika
}

$service1 = new UserService(new FileLogger("app.log"));
$service1->register("Anna");

$service2 = new UserService(new DatabaseLogger());
$service2->register("Piotr");