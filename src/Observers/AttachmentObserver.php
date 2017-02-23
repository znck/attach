<?php namespace Znck\Attach\Observers;

use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;

class AttachmentObserver
{
    /**
     * @var FinderContract
     */
    protected $finder;

    public function getFinder(): FinderContract
    {
        if (is_null($this->finder)) {
            $this->finder = app(FinderContract::class);
        }

        return $this->finder;
    }

    public function deleted(AttachmentContract $attachment)
    {
        $this->getFinder()->unlink($attachment);
    }
}
