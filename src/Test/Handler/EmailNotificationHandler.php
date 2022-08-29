<?php

declare(strict_types=1);

namespace JsonRpc\Test\Handler;

use JsonRpc\NotificationHandler;
use JsonRpc\Request\Notification;

final class EmailNotificationHandler implements NotificationHandler
{
    public function handle(Notification $notification): void
    {
    }
}
