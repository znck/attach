<?php namespace Znck\Attach\Manipulators;

use Illuminate\Container\Container;
use Intervention\Image\ImageManager;
use Znck\Attach\AbstractManipulation;
use Znck\Attach\Contracts\Media;

/**
 * @property int width
 * @property int ratio
 */
class Thumb extends AbstractManipulation
{
    /**
     * @var Container
     */
    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    protected function getWidth()
    {
        return $this->width ?? 200;
    }

    protected function getRatio()
    {
        return $this->ratio ?? 1;
    }

    public function apply(Media $media)
    {
        if ($this->isImage($media->mime)) {
            $this->thumbnailForImage($media);
        }
    }

    public function thumbnailForImage(Media $media)
    {
        $image = $this->intervention()->make($media->getContent());
        $image->interlace();
        $image->fit($this->getWidth(), $this->getWidth() * $this->getRatio());

        $media->setManipulation($this->getName(), $image->encode(), $image->mime());
    }

    /**
     * @return ImageManager
     */
    public function intervention()
    {
        return $this->app->make(ImageManager::class);
    }
}
