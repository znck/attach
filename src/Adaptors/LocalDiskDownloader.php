<?php namespace Znck\Attach\Adaptors;

use Illuminate\Filesystem\FilesystemManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\Downloader;

class LocalDiskDownloader implements Downloader
{
    /**
     * @var \Illuminate\Filesystem\FilesystemManager
     */
    protected $manager;

    /**
     * LocalDiskDownloader constructor.
     *
     * @param \Illuminate\Filesystem\FilesystemManager $manager
     */
    public function __construct(FilesystemManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Respond with file.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract|\Znck\Attach\Attachment $attachment
     * @param string                                                            $variation
     *
     * @return BinaryFileResponse
     */
    public function response(AttachmentContract $attachment, string $variation = null)
    {
        $path = $this->getDisk($attachment)->applyPathPrefix($attachment->getPath($variation));

        $response = new BinaryFileResponse(
            $path, 200,
            $this->prepareHeaders($attachment, $variation), $this->isPublic($attachment),
            null, true
        );

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $attachment->filename);

        return $response;
    }

    /**
     * Download a file.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract|\Znck\Attach\Attachment $attachment
     * @param string|null                                                       $variation
     *
     * @return BinaryFileResponse
     */
    public function download(AttachmentContract $attachment, string $variation = null)
    {
        $response = $this->response($attachment, $variation);

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $attachment->filename);

        return $response;
    }

    /**
     * @param \Znck\Attach\Attachment|\Znck\Attach\Contracts\AttachmentContract $attachment
     *
     * @return \League\Flysystem\Adapter\Local|\Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getDisk($attachment)
    {
        return $this->manager->disk($attachment->disk);
    }

    /**
     * @param \Znck\Attach\Attachment|\Znck\Attach\Contracts\AttachmentContract $attachment
     * @param string|null                                                       $variation
     *
     * @return array
     */
    protected function prepareHeaders($attachment, $variation = null)
    {
        if ($variation) {
            $mime = $attachment->variations[$variation]['mime'] ?? $attachment->mime;
        } else {
            $mime = $attachment->mime;
        }

        return [
            'Content-Type' => $mime,
        ];
    }

    /**
     * @param \Znck\Attach\Attachment|\Znck\Attach\Contracts\AttachmentContract $attachment
     *
     * @return bool
     */
    protected function isPublic($attachment)
    {
        return $attachment->visibility === 'public';
    }
}
