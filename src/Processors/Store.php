<?php namespace Znck\Attach\Processors;

use Illuminate\Http\UploadedFile;
use Znck\Attach\Contracts\Attachment;

class Store extends AbstractProcessor
{
    protected $file;

    public function __construct(UploadedFile $file)
    {
        $this->file = $file;
    }

    protected function apply(Attachment $attachment)
    {
        $this->getFinder()->put($attachment->path, $this->file, $attachment->visibility);
    }

    protected function attach(Attachment $attachment)
    {
        // -- Nothing Here! --
    }
}
