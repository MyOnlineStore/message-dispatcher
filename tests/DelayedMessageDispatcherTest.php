<?php
declare(strict_types=1);

namespace MyOnlineStore\MessageDispatcher\Tests;

use MyOnlineStore\MessageDispatcher\DelayedMessageDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class DelayedMessageDispatcherTest extends TestCase
{
    /** @var EventDispatcherInterface&MockObject */
    private $eventDispatcher;

    /** @var MessageBusInterface&MockObject */
    private $messageBus;

    /** @var DelayedMessageDispatcher&MockObject */
    private $messageDispatcher;

    protected function setUp(): void
    {
        $this->messageDispatcher = $this->getMockBuilder(DelayedMessageDispatcher::class)
            ->onlyMethods(['getPhpSapi'])
            ->setConstructorArgs(
                [
                    $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
                    $this->messageBus = $this->createMock(MessageBusInterface::class),
                ]
            )
            ->getMock();
    }

    public function testDispatchCli(): void
    {
        $this->messageDispatcher->expects(self::once())
            ->method('getPhpSapi')
            ->willReturn('cli');

        $this->eventDispatcher->expects(self::never())
            ->method('addListener');

        $this->messageBus->expects(self::once())
            ->method('dispatch')
            ->with($message = new \stdClass())
            ->willReturn(new Envelope($message));

        $this->messageDispatcher->dispatch($message);
    }

    public function testDispatchFpmWithCall(): void
    {
        $this->messageDispatcher->expects(self::once())
            ->method('getPhpSapi')
            ->willReturn('fpm');

        $this->messageBus->expects(self::once())
            ->method('dispatch')
            ->with($message = new \stdClass())
            ->willReturn(new Envelope($message));

        $this->eventDispatcher->expects(self::once())
            ->method('addListener')
            ->with(
                KernelEvents::TERMINATE,
                self::callback(
                    static function (callable $callable) use ($message): bool {
                        $callable($message);

                        return true;
                    }
                )
            );

        $this->messageDispatcher->dispatch($message);
    }

    public function testDispatchFpmWithoutCall(): void
    {
        $this->messageDispatcher->expects(self::once())
            ->method('getPhpSapi')
            ->willReturn('fpm');

        $this->messageBus->expects(self::never())
            ->method('dispatch');

        $this->eventDispatcher->expects(self::once())
            ->method('addListener')
            ->with(
                KernelEvents::TERMINATE,
                self::callback(
                    static function (): bool {
                        return true;
                    }
                )
            );

        $this->messageDispatcher->dispatch(new \stdClass());
    }

    public function testPhpSapi(): void
    {
        $messageDispatcher = new DelayedMessageDispatcher(
            $this->eventDispatcher,
            $this->messageBus
        );

        $class = new \ReflectionClass($messageDispatcher);
        $reflection = $class->getMethod('getPhpSapi');
        $reflection->setAccessible(true);

        self::assertEquals('cli', $reflection->invokeArgs($messageDispatcher, []));
    }
}
