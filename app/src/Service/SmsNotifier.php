<?php
namespace App\Service;

use Psr\Log\LoggerInterface;

final class SmsNotifier implements NotifierInterface
{
    public function __construct(private LoggerInterface $logger) {}
    public function getKey(): string { return 'sms'; }
    public function notify(string $message): void
    {
        $this->logger->info('[SMS] '.$message);
    }
}
