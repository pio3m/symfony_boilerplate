<?php
namespace App\Service;

use Psr\Log\LoggerInterface;

final class EmailNotifier implements NotifierInterface
{
    public function __construct(private LoggerInterface $logger) {}
    public function getKey(): string { return 'email'; }
    public function notify(string $message): void
    {
        $this->logger->info('[EMAIL] '.$message);
    }
}
