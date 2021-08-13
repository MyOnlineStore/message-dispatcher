<?php
declare(strict_types=1);

namespace MyOnlineStore\MessageDispatcher\Tests;

use MyOnlineStore\MessageDispatcher\SynchronousMessageDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SynchronousMessageDispatcherTest extends TestCase
{
    /** @var MessageBusInterface&MockObject */
    private $messageBus;

    /** @var SynchronousMessageDispatcher */
    private $messageDispatcher;

    protected function setUp(): void
    {
        $this->messageDispatcher = new SynchronousMessageDispatcher(
            $this->messageBus = $this->createMock(MessageBusInterface::class)
        );
    }

    public function testDispatch(): void
    {
        $message = new \stdClass();

        $this->messageBus->expects(self::once())
            ->method('dispatch')
            ->with($message)
            ->willReturn(new Envelope($message));

        $this->messageDispatcher->dispatch($message);
    }
}
