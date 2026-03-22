<?php

namespace Karim\ModelPulse\Events;

use Illuminate\Database\Eloquent\Model;
use Karim\ModelPulse\Models\Message;

class MessageReplied
{
    public function __construct(
        public readonly Model $messageable,
        public readonly Message $parentMessage,
        public readonly Message $replyMessage
    ) {}
}
