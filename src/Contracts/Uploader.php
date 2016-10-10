<?php namespace Znck\Attach\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\Filesystem;

interface Uploader
{
    public function __construct(UploadedFile $file, Attachment $attachment);

    public function upload(): self;

    public function attachTo(Model $model): self;

    public function owner(Model $model): self;

    public function getAttachment(): Attachment;

    public function setStorage(Filesystem $storage): self;
}
