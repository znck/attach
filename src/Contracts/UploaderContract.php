<?php namespace Znck\Attach\Contracts;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface UploaderContract
{
    public static function make(
        UploadedFile $file,
        AttachmentContract $attachment,
        FinderContract $finder
    ): UploaderContract;

    public function upload(): AttachmentContract;

    public function attachTo(Model $related);

    public function owner(Model $owner);

    public function getAttachment(): AttachmentContract;
}
