<?php

declare(strict_types=1);

namespace JsonRpc;

use JsonRpc\Request\Notification;

interface NotificationHandler
{
    public function handle(Notification $notification): void;
}
