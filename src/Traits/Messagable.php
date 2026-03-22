<?php

namespace Karim\ModelPulse\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Karim\ModelPulse\Events\MessageCreated;
use Karim\ModelPulse\Events\MessagePinned;
use Karim\ModelPulse\Events\MessageRemoved;
use Karim\ModelPulse\Events\MessageReplied;
use Karim\ModelPulse\Events\MessagesMarkedRead;
use Karim\ModelPulse\Events\MessageUnpinned;

use Karim\ModelPulse\Models\Message;

trait Messagable
{
    /**
     * Get all messages for this model
     */
    public function messages(): MorphMany
    {

        return $this->morphMany(Message::class, 'messageable')
            ->whereNot('type', 'activity')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all messages with filters
     */
    public function withFilters($filters)
    {
        $query = $this->messages();

        $this->applyMessageFilters($query, $filters);

        return $query->get();
    }

    /**
     * Apply filters to the query
     */
    private function applyMessageFilters($query, array $filters)
    {
        if (! empty($filters['type'])) {
            $query->whereIn('type', $filters['type']);
        }

        if (isset($filters['is_internal'])) {
            $query->where('is_internal', $filters['is_internal']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);

            if (! empty($filters['causer_type'])) {
                $query->where('causer_type', $filters['causer_type']);
            }
        }
        if (! empty($filters['assignable_id'])) {
            $query->where('assignable_id', $filters['assignable_id']);

            if (! empty($filters['assignable_type'])) {
                $query->where('assignable_type', $filters['assignable_type']);
            }
        }

        if (! empty($filters['activity_type_id'])) {
            $query->where('activity_type_id', $filters['activity_type_id']);
        }

        if (! empty($filters['search'])) {
            $searchTerm = '%'.$filters['search'].'%';

            $query->where(function ($query) use ($searchTerm) {
                $query->where('subject', 'like', $searchTerm)
                    ->orWhere('body', 'like', $searchTerm)
                    ->orWhere('summary', 'like', $searchTerm)
                    ->orWhere('name', 'like', $searchTerm);
            });
        }

        return $query;
    }

    /**
     * Get all read messages
     */
    public function unRead()
    {
        return $this->messages()->where('is_read', false)->get();
    }

    /**
     * Mark all unread messages as read.
     */
    public function markAsRead(): int
    {
        $affectedRows = $this->messages()->where('is_read', false)->update(['is_read' => true]);

        if ($affectedRows > 0) {
            event(new MessagesMarkedRead($this, $affectedRows));
        }

        return $affectedRows;
    }

    /**
     * Get all activity messages for this model
     */
    public function activities(): MorphMany
    {

        return $this->morphMany(Message::class, 'messageable')
            ->where('type', 'activity')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activity plans for this model
     */
    public function activityPlans(): mixed
    {
        return collect();
    }

    /**
     * Get partners
     */


    /**
     * Add a new message
     */
    public function addMessage(array $data): Message
    {
        $message = new Message;

        $user =  Auth::user();

        $message->fill(array_merge([
            'date_deadline' => $data['date_deadline'] ?? now(),
            'causer_type'   => $data['causer_type'] ?? ($user instanceof Model ? $user->getMorphClass() : $this->getMorphClass()),
            'causer_id'     => $data['causer_id'] ?? $user?->id ?? $this->getKey(),
            'assignable_type' => $data['assignable_type'] ?? ($user instanceof Model ? $user->getMorphClass() : $this->getMorphClass()),
            'assignable_id' => $data['assignable_id'] ?? $user?->id ?? $this->getKey(),
        ], $data));

        $this->messages()->save($message);
        event(new MessageCreated($this, $message));

        return $message;
    }



    /**
     * Add a reply to an existing message
     */
    public function replyToMessage(Message $parentMessage, array $data): Message
    {
        $replyMessage = $this->addMessage(array_merge($data, [
            'parent_id'        => $parentMessage->id,
            'company_id'       => $parentMessage->company_id,
            'activity_type_id' => $parentMessage->activity_type_id,
        ]));

        event(new MessageReplied($this, $parentMessage, $replyMessage));

        return $replyMessage;
    }

    /**
     * Remove a message
     */
    public function removeMessage($messageId, $type = 'messages'): bool
    {
        $message = $this->{$type}()->find($messageId);

        if (! $message) {
            return false;
        }

        if (
            $message->messageable_id !== $this->id
            || $message->messageable_type !== get_class($this)
        ) {
            return false;
        }

        $deleted = $message->delete();

        if ($deleted) {
            event(new MessageRemoved($this, $message));
        }

        return $deleted;
    }

    /**
     * Pin a message
     */
    public function pinMessage(Message $message): bool
    {

        if (
            $message->messageable_id !== $this->id
            || $message->messageable_type !== get_class($this)
        ) {
            return false;
        }

        $message->pinned_at = now();

        $saved = $message->save();

        if ($saved) {
            event(new MessagePinned($this, $message));
        }

        return $saved;
    }

    /**
     * Unpin a message
     */
    public function unpinMessage(Message $message): bool
    {

        if (
            $message->messageable_id !== $this->id
            || $message->messageable_type !== get_class($this)
        ) {
            return false;
        }

        $message->pinned_at = null;

        $saved = $message->save();

        if ($saved) {
            event(new MessageUnpinned($this, $message));
        }

        return $saved;
    }

    /**
     * Get all pinned messages
     */
    public function getPinnedMessages(): Collection
    {
        return $this->messages()->whereNotNull('pinned_at')->orderBy('pinned_at', 'desc')->get();
    }

    /**
     * Get messages by type
     */
    public function getMessagesByType(string $type): Collection
    {
        return $this->messages()->where('type', $type)->get();
    }

    /**
     * Get internal messages
     */
    public function getInternalMessages(): Collection
    {
        return $this->messages()->where('is_internal', true)->get();
    }

    /**
     * Get messages by date range
     */
    public function getMessagesByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->messages()
            ->whereBetween('date_deadline', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get messages by activity type
     */
    public function getMessagesByActivityType(int $activityTypeId): Collection
    {
        return $this->messages()
            ->where('activity_type_id', $activityTypeId)
            ->get();
    }







}
