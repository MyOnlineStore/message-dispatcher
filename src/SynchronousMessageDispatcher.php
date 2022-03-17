<?php
declare(strict_types=1);

namespace MyOnlineStore\MessageDispatcher;

use Symfony\Component\Messenger\MessageBusInterface;

final class SynchronousMessageDispatcher implements MessageDispatcher
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $message): void
    {
        $this->messageBus->dispatch($message);
    }
}
