<?php

namespace Znck\Attach;

use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Traits\AttachmentTrait;

/**
 * Attachment Model.
 *
 * @property string $path       Absolute file path.
 * @property string $title      Preview name for file.
 * @property string $mime       File mime type.
 * @property string $extension  File extension.
 * @property int $size          File size in bytes.
 * @property string $visibility File visibility.
 * @property string $filename   Original filename.
 * @property string $disk       Filesystem disk.
 * @property array $variations  File variations like thumbnails or previews.
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Attachment extends Model implements AttachmentContract
{
    use AttachmentTrait;

    public function owner()
    {
        return $this->belongsTo(
            config('attach.user', config('auth.providers.users.model'))
        );
    }

    public function related()
    {
        return $this->morphTo();
    }
}
