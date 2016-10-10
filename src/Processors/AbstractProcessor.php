<?php namespace Znck\Attach\Processors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\Filesystem;
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

    /**
     * @return Finder
     */
    public function getFinder(): Finder {
        if (!$this->finder) {
            $this->finder = app(Finder::class);
        }

        return $this->finder;
    }

    /**
     * @param Filesystem $storage
     */
    public function setStorage(Filesystem $storage) {
        $this->finder->setStorage($storage);
    }
}
