<?php namespace Znck\Attach\Processors;

use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;
use Znck\Attach\Contracts\ProcessorContract;

abstract class AbstractProcessorContract implements ProcessorContract
{
    /**
     * @var AttachmentContract|Model
     */
    protected $attachment;

    /**
     * @var FinderContract
     */
    protected $finder;

    /**
     * @return FinderContract
     */
    public function getFinder(): FinderContract
    {
        if (! $this->finder) {
            $this->finder = app(FinderContract::class);
        }

        return $this->finder;
    }
}
