<?php


namespace Karim\ModelPulse\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Karim\ModelPulse\Models\Attachment;

trait Attachable
{

    /**
     * Get all attachments for this model
     */
    public function attachments(): MorphMany
    {

        return $this->morphMany(Attachment::class, 'messageable')->orderBy('created_at', 'desc');
    }

    /**
     * Add multiple attachments
     */
    public function addAttachments(array $files, array $additionalData = []): Collection
    {
        if (empty($files)) {
            return collect();
        }

        $disk = $this->resolveAttachmentDisk();

        return $this->attachments()
            ->createMany(
                collect($files)
                    ->map(function ($filePath) use ($disk, $additionalData) {
                        $authUser = Auth::user();
                        $fileContent = Storage::disk($disk)->get($filePath);
                        $fileSize = mb_strlen($fileContent, '8bit');
                        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($fileContent) ?: 'application/octet-stream';

                        return [
                            'file_path'          => $filePath,
                            'original_file_name' => basename($filePath),
                            'mime_type'          => $mimeType,
                            'file_size'          => $fileSize,
                            'creator_id'         => Auth::id(),
                            'creator_type'       => $authUser instanceof \Illuminate\Database\Eloquent\Model ? $authUser->getMorphClass() : null,
                            ...$additionalData,
                        ];
                    })
                    ->filter()
                    ->toArray()
            );
    }

    /**
     * Remove an attachment
     */
    public function removeAttachment($attachmentId): bool
    {
        $attachment = $this->attachments()->find($attachmentId);

        if (
            ! $attachment ||
            $attachment->messageable_id !== $this->id ||
            $attachment->messageable_type !== get_class($this)
        ) {
            return false;
        }

        $disk = $this->resolveAttachmentDisk();

        if (Storage::disk($disk)->exists($attachment->file_path)) {
            Storage::disk($disk)->delete($attachment->file_path);
        }

        return $attachment->delete();
    }

    /**
     * Get attachments by type
     */
    public function getAttachmentsByType(string $mimeType): Collection
    {
        return $this->attachments()
            ->where('mime_type', 'LIKE', $mimeType.'%')
            ->get();
    }

    /**
     * Get attachments by date range
     */
    public function getAttachmentsByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->attachments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get all image attachments
     */
    public function getImageAttachments(): Collection
    {
        return $this->getAttachmentsByType('image/');
    }

    /**
     * Get all document attachments
     */
    public function getDocumentAttachments(): Collection
    {
        return $this->attachments()
            ->where('mime_type', 'NOT LIKE', 'image/%')
            ->get();
    }

    /**
     * Check if file exists
     */
    public function attachmentExists($attachmentId): bool
    {
        $attachment = $this->attachments()->find($attachmentId);

        return $attachment && Storage::disk($this->resolveAttachmentDisk())->exists($attachment->file_path);
    }

    protected function resolveAttachmentDisk(): string
    {
        if (method_exists($this, 'getModelPulseAttachmentDisk')) {
            $modelDisk = $this->getModelPulseAttachmentDisk();

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
