<?php

use Illuminate\Support\Facades\Storage;
use Karim\ModelPulse\Models\Attachment;
use Tests\Fixtures\Models\TestProject;
use Tests\Fixtures\Models\TestProjectCustomDisk;

it('sets creator fields and resolves url', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Subject']);

    $attachment = Attachment::query()->create([
        'creator_type' => $user->getMorphClass(),
        'creator_id' => $user->id,
        'message_id' => $message->id,
        'messageable_type' => $project->getMorphClass(),
        'messageable_id' => $project->id,
        'file_path' => 'attachments/file.txt',
        'name' => 'file',
        'original_file_name' => 'file.txt',
        'mime_type' => 'text/plain',
    ]);

    expect($attachment->creator_id)->toBe($user->id);
    expect($attachment->url)->toContain('attachments/file.txt');
});

it('deletes file from public disk on attachment deletion', function () {
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Subject']);

    Storage::disk('public')->put('attachments/remove-me.txt', 'abc');

    $attachment = Attachment::query()->create([
        'creator_type' => $user->getMorphClass(),
        'creator_id' => $user->id,
        'message_id' => $message->id,
        'messageable_type' => $project->getMorphClass(),
        'messageable_id' => $project->id,
        'file_path' => 'attachments/remove-me.txt',
        'name' => 'file',
        'original_file_name' => 'remove-me.txt',
        'mime_type' => 'text/plain',
    ]);

    $removed = $project->removeAttachment($attachment->id);

    expect($removed)->toBeTrue();
    expect(Storage::disk('public')->exists('attachments/remove-me.txt'))->toBeFalse();
});

it('uses configured disk for attachment operations', function () {
    app('config')->set('model-pulse.attachments.disk', 's3');
    $user = test()->actingAs();
    $project = TestProject::query()->create(['name' => 'P1', 'owner_id' => $user->id]);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Subject']);

    Storage::disk('s3')->put('attachments/config-disk.txt', 'abc');

    $attachments = $project->addAttachments(['attachments/config-disk.txt'], [
        'message_id' => $message->id,
        'name' => 'config',
    ]);

    expect($attachments)->toHaveCount(1);
    expect($project->attachmentExists($attachments->first()->id))->toBeTrue();

    $project->removeAttachment($attachments->first()->id);

    expect(Storage::disk('s3')->exists('attachments/config-disk.txt'))->toBeFalse();
});

it('uses model disk override before config disk', function () {
    app('config')->set('model-pulse.attachments.disk', 'public');
    $user = test()->actingAs();
    $project = TestProjectCustomDisk::query()->create(['name' => 'P1', 'owner_id' => $user->id]);
    $message = $project->addMessage(['type' => 'note', 'subject' => 'Subject']);

    Storage::disk('s3')->put('attachments/model-disk.txt', 'abc');

    $attachments = $project->addAttachments(['attachments/model-disk.txt'], [
        'message_id' => $message->id,
        'name' => 'model',
    ]);

    expect($attachments)->toHaveCount(1);
    expect(Storage::disk('public')->exists('attachments/model-disk.txt'))->toBeFalse();
    expect($project->attachmentExists($attachments->first()->id))->toBeTrue();

    $project->removeAttachment($attachments->first()->id);

    expect(Storage::disk('s3')->exists('attachments/model-disk.txt'))->toBeFalse();
});
