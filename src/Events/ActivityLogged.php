<?php

namespace Karim\ModelPulse\Events;

use Illuminate\Database\Eloquent\Model;
use Karim\ModelPulse\Models\Message;

class ActivityLogged
{
    public function __construct(
        public readonly Model $model,
        public readonly string $event,
        public readonly Message $activityMessage
    ) {}
}
