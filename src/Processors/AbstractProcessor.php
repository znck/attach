<?php namespace Znck\Attach\Processors;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Finder;
use Znck\Attach\Contracts\Processor;

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

    /**
     * @return Finder
     */
    public function getFinder(): Finder
    {
        if (! $this->finder) {
            $this->finder = app(Finder::class);
        }

        return $this->finder;
    }

    /**
     * @param Filesystem $storage
     */
    public function setStorage(Filesystem $storage)
    {
        $this->finder->setStorage($storage);
    }
}
