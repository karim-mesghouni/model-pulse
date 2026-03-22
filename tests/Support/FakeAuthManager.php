<?php

namespace Tests\Support;

class FakeAuthManager
{
    private mixed $user = null;

    public function user(): mixed
    {
        return $this->user;
    }

    public function id(): mixed
    {
        return $this->user?->id;
    }

    public function setUser(mixed $user): void
    {
        $this->user = $user;
    }
}
