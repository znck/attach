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
        int $width = 1600,
        string $name = null,
        int $height = null,
        string $mime = null
    ) {
        $this->name = $name;
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
    }

    public function getImageManager() : ImageManager
    {
        if (! $this->imageManager) {
            $this->imageManager = app(ImageManager::class);
        }

        return $this->imageManager;
    }

    public function setImageManager(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function process(Attachment $attachment)
    {
        $storage = $this->getFinder();
        $path = ! is_null($this->name) ? $storage->getPath($attachment, $this->name) : $attachment->path;

        $image = $this->getImageManager()->make($storage->get($attachment->path));

        if (is_null($this->height)) {
            $this->height = (int) ($this->width / $image->width() * $image->height());
        }

        $image->interlace()->fit($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        });

        $format = $this->mime ?? $attachment->mime;
        $storage->put($path, $image->encode($format), $attachment->visibility);

        if (! is_null($this->name)) {
            $attachment->variations[$this->name] = [
                'mime' => $this->mime,
                'size' => $storage->size($path),
            ];
        } else {
            $attachment->size = $storage->size($path);
        }
    }
}
