<?php
declare(strict_types=1);

namespace MyOnlineStore\MessageDispatcher;

interface MessageDispatcher
{
    public function dispatch(object $message): void;
}
