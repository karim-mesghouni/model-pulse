<?php

namespace Karim\ModelPulse\Events;

use Illuminate\Database\Eloquent\Model;

class MessagesMarkedRead
{
    public function __construct(
        public readonly Model $messageable,
        public readonly int $affectedRows
    ) {}
}
