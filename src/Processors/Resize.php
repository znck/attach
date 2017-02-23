<?php namespace Znck\Attach\Processors;

use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use Znck\Attach\Contracts\AttachmentContract;

class Resize extends AbstractProcessorContract
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

    public function getImageManager(): ImageManager
    {
        if (!$this->imageManager) {
            $this->imageManager = app(ImageManager::class);
        }

        return $this->imageManager;
    }

    public function setImageManager(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @param \Znck\Attach\Contracts\AttachmentContract|\Znck\Attach\Attachment $attachment
     */
    public function process(AttachmentContract $attachment)
    {
        if (!$this->isImage($attachment->mime)) {
            return;
        }

        $finder = $this->getFinder();

        $image = $this->getImageManager()->make($finder->get($attachment));

        if (is_null($this->height)) {
            $this->height = (int)($this->width / $image->width() * $image->height());
        }

        $image->interlace()->fit($this->width, $this->height, function (Constraint $constraint) {
            $constraint->upsize();
        });

        $format = is_null($this->mime) ? $attachment->mime : $this->mime;
        $finder->put($attachment->getPath($this->name), $image->encode($format), $attachment->visibility);

        if (!is_null($this->name)) {
            $attachment->variations = (array)$attachment->variations + [
                    $this->name => [
                        'mime' => $format,
                    ],
                ];
        }
    }

    protected function isImage(string $mime)
    {
        return preg_match('/^image\/(jpe?g|png|tiff|gif|bmp)$/i', $mime) === 1;
    }
}
