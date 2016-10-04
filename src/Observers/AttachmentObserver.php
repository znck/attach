<?php namespace Znck\Attach\Observers;

use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Finder;

class AttachmentObserver
{
    /**
     * @var Finder
     */
    protected $finder;

    public function getFinder() : Finder {
        if (is_null($this->finder)) {
            $this->finder = app(Finder::class);
        }

        return $this->finder;
    }

    public function deleted(Attachment $attachment) {

    }
}
