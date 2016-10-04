<?php namespace Znck\Attach\Processors;

use Intervention\Image\ImageManager;
use Znck\Attach\Contracts\Attachment;

class Resize extends AbstractProcessor
{
    protected $imageManager;

    protected $name;

    protected $width;

    protected $mime;

    protected $height;

    public function __construct(
        string $name = null,
        int $width = 1600,
        int $height = null,
        string $mime = 'image/jpg'
    ) {
        $this->name = $name;
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
    }

    public function getImageManager() : ImageManager {
        if (is_null($this->imageManager)) {
            $this->imageManager = app(ImageManager::class);
        }

        return $this->imageManager;
    }

    public function setImageManager(ImageManager $imageManager) {
        $this->imageManager = $imageManager;
    }

    protected function apply(Attachment $attachment) {
        $storage = $this->getFinder();
        $path = !is_null($this->name) ? $storage->getPath($attachment, $this->name) : $attachment->path;
        $image = $this->getImageManager()->make($storage->get($attachment->path));
        $image->interlace()->fit($this->width, $this->width);
        $format = $this->name ? $attachment->mime : 'image/jpg';
        $storage->put($path, $image->encode($format), $attachment->visibility);

        if (!is_null($this->name)) {
            $attachment->variations[$this->name]['size'] = $storage->size($path);
        } else {
            $attachment->size = $storage->size($path);
        }
    }

    protected function attach(Attachment $attachment) {
        if (!is_null($this->name)) {
            $attachment->variations[$this->name] = [
                'mime' => $this->mime,
                'size' => 0,
            ];
        }
    }
}
