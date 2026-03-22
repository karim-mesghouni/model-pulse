# Model Pulse

Add collaboration, messaging, attachments, activity logs, and domain events to any Eloquent model.

## Features
- Follow/unfollow support for any model (`Followable`)
- Thread-like messaging APIs (`Messagable`)
- File attachment helpers (`Attachable`)
- Automatic model activity logging (`HasLogActivity`)
- Typed Laravel events for messaging and activity operations

## Requirements
- PHP 8.3+
- Laravel components:
  - `illuminate/container` ^12|^13
  - `illuminate/contracts` ^12|^13
  - `illuminate/database` ^12|^13

## Installation
```bash
composer require karimms/model-pulse
```

Run package migrations:

```bash
php artisan migrate --path=vendor/karimms/model-pulse/database/migrations
```

## Quick Start
Add traits to your Eloquent model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Karim\ModelPulse\Traits\Attachable;
use Karim\ModelPulse\Traits\Followable;
use Karim\ModelPulse\Traits\HasLogActivity;
use Karim\ModelPulse\Traits\Messagable;

class Project extends Model
{
    use Followable;
    use Messagable;
    use Attachable;
    use HasLogActivity;

    public function getModelTitle(): string
    {
        return $this->name ?? 'Project';
    }

    public function getLogAttributeLabels(): array
    {
        return [
            'name' => 'Name',
            'status' => 'Status',
        ];
    }
}
```

## Usage

### Collaboration (Followers)
```php
$project->addFollower($user);
$project->isFollowedBy($user);
$project->removeFollower($user);
```

### Messaging
```php
$message = $project->addMessage([
    'type' => 'note',
    'subject' => 'Kickoff',
    'body' => 'Project started',
]);

$reply = $project->replyToMessage($message, [
    'type' => 'note',
    'subject' => 'Re: Kickoff',
    'body' => 'Acknowledged',
]);

$project->markAsRead();
$project->pinMessage($message);
$project->unpinMessage($message);
$project->removeMessage($message->id);
```

### Attachments
```php
$attachments = $project->addAttachments([
    'uploads/spec.pdf',
    'uploads/mockup.png',
]);

$project->getImageAttachments();
$project->getDocumentAttachments();
$project->removeAttachment($attachments->first()->id);
```

### Custom Storage Disk
Set a global attachment disk in configuration:

```php
// config/model-pulse.php
return [
    'attachments' => [
        'disk' => 's3',
    ],
];
```

Optional per-model override:

```php
public function getModelPulseAttachmentDisk(): ?string
{
    return 's3';
}
```

Resolution order:
- Model override (`getModelPulseAttachmentDisk`)
- `model-pulse.attachments.disk`
- `filesystems.default`

### Activity Logging
`HasLogActivity` automatically records model lifecycle activity and stores audit-style entries through the package message model.

You can also trigger logging manually:

```php
$project->logModelActivity('updated');
```

## Events
The package dispatches typed Laravel events after successful messaging/activity operations.

### Messaging Events
- `Karim\ModelPulse\Events\MessageCreated`
- `Karim\ModelPulse\Events\MessageReplied`
- `Karim\ModelPulse\Events\MessageRemoved`
- `Karim\ModelPulse\Events\MessagesMarkedRead`
- `Karim\ModelPulse\Events\MessagePinned`
- `Karim\ModelPulse\Events\MessageUnpinned`

### Activity Event
- `Karim\ModelPulse\Events\ActivityLogged`

### Listening to Events
Example listener registration in your `EventServiceProvider`:

```php
use App\Listeners\HandleMessageCreated;
use Karim\ModelPulse\Events\MessageCreated;

protected $listen = [
    MessageCreated::class => [
        HandleMessageCreated::class,
    ],
];
```

## Notes
- Events are dispatched only when operations succeed.
- Failed or no-op operations do not dispatch success events.
