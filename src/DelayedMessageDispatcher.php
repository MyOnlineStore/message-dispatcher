<?php
declare(strict_types=1);

namespace MyOnlineStore\MessageDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

/** @final */ class DelayedMessageDispatcher implements MessageDispatcher
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(EventDispatcherInterface $eventDispatcher, MessageBusInterface $messageBus)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $message): void
    {
        if ('cli' === $this->getPhpSapi()) {
            $this->messageBus->dispatch($message);
        } else {
            $this->eventDispatcher->addListener(
                KernelEvents::TERMINATE,
                function () use ($message): void {
                    $this->messageBus->dispatch($message);
                }
            );
        }
    }

    protected function getPhpSapi(): string
    {
        return \PHP_SAPI;
    }
}
