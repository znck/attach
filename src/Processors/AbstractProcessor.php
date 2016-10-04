<?php namespace Znck\Attach\Processors;

use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Finder;
use Znck\Attach\Contracts\Processor;
use Znck\Attach\Contracts\Storage;

abstract class AbstractProcessor implements Processor
{
    /**
     * @var Attachment|Model
     */
    protected $attachment;

    /**
     * @var Finder
     */
    protected $finder;

    public function process(Attachment $attachment)
    {
        $this->attachment = $attachment;

        $this->attach($attachment);

        $this->attachment->saved(
            function (Attachment $attachment) {
                $this->getFinder()->useDisk($attachment->disk);
                $this->apply($attachment);
            }
        );
    }

    abstract protected function apply(Attachment $attachment);

    abstract protected function attach(Attachment $attachment);

    /**
     * @return Finder
     */
    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * @param Storage $storage
     */
    public function setStorage(Storage $storage)
    {
        $this->finder->setStorage($storage);
    }
}
