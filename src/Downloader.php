<?php namespace Znck\Attach;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Downloader as DownloaderInterface;
use Znck\Attach\Contracts\Finder;

class Downloader implements DownloaderInterface
{
    /**
     * Database entry for uploaded attachment.
     *
     * @var Model|Attachment
     */
    protected $attachment;

    /**
     * UUID key for uploaded attachment.
     *
     * @var string
     */
    protected $uuid;

    /**
     * Requested variation of the uploaded attachment.
     *
     * @var string|null
     */
    protected $variation;

    /**
     * File extension expected. (This is useful for caching files different types of files).
     *
     * @var string|null
     */
    protected $extension;

    /**
     * A file system helper.
     *
     * @var Finder
     */
    protected $finder;

    public function parseFilename(string $filename)
    {
        $parts = explode('.', $filename, 3);

        switch (count($parts)) {
            case 1:
                $this->uuid = $parts[0];
                break;
            case 2:
                list($this->uuid, $this->extension) = $parts;
                break;
            case 3:
                list($this->uuid, $this->variation, $this->extension) = $parts;
                break;
            default:
                throw new NotFoundHttpException();
        }
    }

    public function getRequestedMeta(): array
    {
        if ($this->isVariationRequested()) {
            return $this->getAttachmentVariationMeta($this->getVariation());
        }

        return $this->getAttachmentMeta();
    }

    public function getAttachmentMeta(): array
    {
        $attach = $this->attachment;

        return [
            'filename' => $attach->filename,
            'mime'     => $attach->mime,
            'size'     => $attach->size,
            'title'    => $attach->title,
        ];
    }

    public function getAttachmentVariationMeta(string $name): array
    {
        $attach = $this->attachment;
        if (! array_key_exists($name, $attach->variations)) {
            throw new NotFoundHttpException();
        }
        $variation = $attach->variations[$name];

        return [
            'filename' => $attach->filename,
            'mime'     => $variation['mime'],
            'size'     => $variation['size'],
            'title'    => $attach->title,
        ];
    }

    public function getRequestedFile(): string
    {
        if ($this->isVariationRequested()) {
            return $this->getFinder()->getPath($this->attachment, $this->getVariation());
        }

        return $this->attachment->path;
    }

    public function findAttachment(string $filename): Attachment
    {
        $this->parseFilename($filename);

        $model = app(Attachment::class);

        $this->attachment = $model->where($model->getAttachmentKeyName(), $this->getUuid())->firstOrFail();

        $this->getFinder()->useDisk($this->attachment->disk);

        return $this->attachment;
    }

    /**
     * UUID key for uploaded attachment.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Requested variation of the uploaded attachment.
     *
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    protected function isVariationRequested(): bool
    {
        return ! is_null($this->variation);
    }

    public function getFinder(): Finder
    {
        if (is_null($this->finder)) {
            $this->finder = app(Finder::class);
        }

        return $this->finder;
    }

    public function response(string $filename = null): Response
    {
        return $this->respond($filename);
    }

    public function download(string $filename = null): Response
    {
        return $this->respond(
            $filename,
            ['Content-Disposition' => "attachment; filename=\"{$this->attachment->filename}\""]
        );
    }

    /**
     * @param string $filename
     * @param array  $headers
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function respond(string $filename, $headers = []): StreamedResponse
    {
        $this->findAttachment($filename);

        $meta = $this->getRequestedMeta();
        /** @var ResponseFactory $response */
        $response = app(ResponseFactory::class);

        return $response->file($this->getRequestedFile(), 200, $headers + ['Content-Type' => $meta['mime']]);
    }
}
