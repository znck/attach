<?php namespace Znck\Attach\Manipulators;

use Intervention\Image\ImageManager;
use Znck\Attach\AbstractManipulation;
use Znck\Attach\Contracts\Media;

/**
 * @property  int|null width
 * @property  int|null height
 * @property  \Closure constraints
 * @property  float|null scale
 * @property  string name
 */
class Resize extends AbstractManipulation
{
    /**
     * @var ImageManager
     */
    protected $manager;

    /**
     * @var \Intervention\Image\Image
     */
    protected $image;

    /**
     * @var array
     */
    protected $attributes = ['scale' => .5];

    public function __construct(ImageManager $manager)
    {
        $this->manager = $manager;
    }

    public function apply(Media $media)
    {
        if (! $this->isImage($media->mime)) {
            return;
        }

        $this->image = $this->manager->make($media->getContent());

        $this->image->interlace();
        $this->image->resize($this->getWidth(), $this->getHeight(), $this->getConstraints());
        $media->setManipulation($this->getName(), $this->image->encode(), $this->image->mime());
    }

    /**
     * @return int
     */
    protected function getWidth()
    {
        return $this->width ?? ($this->scale ? $this->image->width() * $this->scale : null);
    }

    /**
     * @return int
     */
    protected function getHeight()
    {
        return $this->height ?? ($this->scale ? $this->image->height() * $this->scale : null);
    }

    protected function getConstraints()
    {
        return $this->constraints ?? function ($constraints) {
            /* @var \Intervention\Image\Constraint $constraints */
            $constraints->upsize();
            $constraints->aspectRatio();
        };
    }

    public function getName() : string
    {
        return $this->name ?? $this->width ?? $this->height ?? $this->scale ?? parent::getName();
    }
}
