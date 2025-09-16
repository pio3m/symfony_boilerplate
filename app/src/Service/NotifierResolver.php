<?php
namespace App\Service;

final class NotifierResolver
{
    /** @param iterable<NotifierInterface> $notifiers */
    public function __construct(private iterable $notifiers) {}

    public function byKey(string $key): NotifierInterface
    {
        foreach ($this->notifiers as $n) {
            if ($n->getKey() === $key) {
                return $n;
            }
        }
        throw new \RuntimeException("Unknown notifier: ".$key);
    }
}
