<?php namespace Znck\Attach\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

/**
 * @property string $path       Absolute file path.
 * @property string $title      Preview name for file.
 * @property string $mime       File mime type.
 * @property string $extension  File extension.
 * @property int    $size       File size in bytes.
 * @property string $visibility File visibility.
 * @property string $filename   Original filename.
 * @property string $disk       Filesystem disk.
 * @property array $variations  File variations like thumbnails or previews.
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
interface Attachment
{
    public function getAttachmentKey() : string;

    public function getAttachmentKeyName() : string;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner();

    /**
     * @return MorphOneOrMany
     */
    public function related();
}
