<?php

namespace  Karim\ModelPulse\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Karim\ModelPulse\Models\Follower;

trait Followable
{
    /**
     * Get all followers of this model.
     */
    public function followers(): MorphMany
    {
        return $this->morphMany(Follower::class, 'followable');
    }

    /**
     * Add a follower to this model
     */
    public function addFollower(Model $follower): Follower
    {
        return $this->followers()->firstOrCreate(
            [
                'follower_id' => $follower->id,
                'follower_type' => $follower->getMorphClass(),
            ],
            [
                'followed_at' => now(),
            ]
        );
    }


    /**
     * Remove a follower from this model
     */
    public function removeFollower(Model $follower): bool
    {
        return (bool) $this->followers()
            ->where('follower_id', $follower->id)
            ->where('follower_type', $follower->getMorphClass())
            ->delete();
    }

    /**
     * Check if a partner is following this model
     */
    public function isFollowedBy(Model $follower): bool
    {
        return $this->followers()
            ->where('follower_id', $follower->id)
            ->where('follower_type', $follower->getMorphClass())

            ->exists();
    }


}