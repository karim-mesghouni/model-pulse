<?php

namespace Karim\ModelPulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'model_pulse_attachments';

    protected $fillable = [
        'creator_id',
        'creator_type',
        'message_id',
        'file_size',
        'name',
        'messageable_type',
        'messageable_id',
        'file_path',
        'original_file_name',
        'mime_type',
    ];

    protected $appends = ['url'];

    public function messageable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->morphTo();
    }


    public function getUrlAttribute(): string
    {
        $disk = Storage::disk($this->resolveAttachmentDisk());

        if (method_exists($disk, 'url')) {
            return call_user_func([$disk, 'url'], $this->file_path);
        }

        return Storage::url($this->file_path);
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $user = Auth::user();
            $data->creator_type ??= $user instanceof Model ? $user->getMorphClass() : null;
            $data->creator_id ??= $user?->id;
        });

        static::deleted(function ($attachment) {
            $filePath = $attachment->file_path;

            if (! $filePath) {
                return;
            }

            Storage::disk($attachment->resolveAttachmentDisk())->delete($filePath);
        });
    }

    protected function resolveAttachmentDisk(): string
    {
        $messageable = $this->messageable;

        if (
            $messageable
            && method_exists($messageable, 'getModelPulseAttachmentDisk')
        ) {
            $modelDisk = $messageable->getModelPulseAttachmentDisk();
            if (is_string($modelDisk) && $modelDisk !== '') {
                return $modelDisk;
            }
        }

        if (app()->bound('config')) {
            $configuredDisk = app('config')->get('model-pulse.attachments.disk');
            if (is_string($configuredDisk) && $configuredDisk !== '') {
                return $configuredDisk;
            }

            $filesystemDefaultDisk = app('config')->get('filesystems.default');
            if (is_string($filesystemDefaultDisk) && $filesystemDefaultDisk !== '') {
                return $filesystemDefaultDisk;
            }
        }

        return 'public';
    }
}
