<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\NotificationHandler;
use JsonRpc\Request\Notification;
use RuntimeException;

final class ExceptionThrowingNotificationHandler implements NotificationHandler
{
    public function handle(Notification $notification): void
    {
        throw new RuntimeException();
    }
}
