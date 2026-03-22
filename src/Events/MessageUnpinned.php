<?php

namespace Karim\ModelPulse\Events;

use Illuminate\Database\Eloquent\Model;
use Karim\ModelPulse\Models\Message;

class MessageUnpinned
{
    public function __construct(
        public readonly Model $messageable,
        public readonly Message $message
    ) {}
}
