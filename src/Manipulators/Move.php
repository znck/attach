<?php namespace Znck\Attach\Manipulators;

use Znck\Attach\AbstractManipulation;
use Znck\Attach\Contracts\Media;

/**
 * @property string path
 */
class Move extends AbstractManipulation
{
    public function apply(Media $media)
    {
        if (! $this->path) {
            return;
        }

        $media->path = $this->path;
    }
}
