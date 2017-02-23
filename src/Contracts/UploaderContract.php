<?php namespace Znck\Attach\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface UploaderContract
{
    public function __construct(UploadedFile $file, AttachmentContract $attachment);

    public function upload(): AttachmentContract;

    public function attachTo(Model $related);

    public function owner(Model $owner);

    public function getAttachment(): AttachmentContract;
}
