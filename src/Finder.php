<?php

namespace Znck\Attach;

use Illuminate\Filesystem\FilesystemManager;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;
use Znck\Attach\Exceptions\InvalidDiskException;

class Finder implements FinderContract
{
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $manager;

    /**
     * @var \Znck\Attach\DownloaderFactory
     */
    protected $adaptor;

    /**
     * Laravel disk configurations.
     *
     * @var array
     */
    protected $disks;

    /**
     * Default disk.
     *
     * @var string
     */
    protected $default;

    /**
     * Finder constructor.
     *
     * @param \Illuminate\Filesystem\FilesystemManager $manager Laravel filesystem manager.
     * @param \Znck\Attach\DownloaderFactory $adaptor           Downloader factory.
     * @param array $disks                                      Disk configurations.
     * @param string $default                                   Default disk.
     */
    public function __construct(
        FilesystemManager $manager,
        DownloaderFactory $adaptor,
        array $disks,
        string $default
    ) {
        $this->disks = $disks;
        $this->default = $default;
        $this->manager = $manager;
        $this->adaptor = $adaptor;
    }

    /**
     * Get original file contents.
     *
     * @param AttachmentContract|Attachment $attachment
     * @param string $variation
     *
     * @return string
     */
    public function get(AttachmentContract $attachment, string $variation = null)
    {
        return $this->getDisk($attachment)->get($attachment->getPath($variation));
    }

    /**
     * Respond with file.
     *
     * @param AttachmentContract|Attachment $attachment
     * @param string $variation
     *
     * @return \Illuminate\Http\Response
     */
    public function response(AttachmentContract $attachment, string $variation = null)
    {
        return $this->adaptor->adaptor($this->manager, $this->getDriver($attachment))
                             ->download($attachment, $variation);
    }

    /**
     * Download a file.
     *
     * @param AttachmentContract $attachment
     * @param string|null $variation
     *
     * @return \Illuminate\Http\Response
     */
    public function download(AttachmentContract $attachment, string $variation = null)
    {
        return $this->adaptor->adaptor($this->manager, $this->getDriver($attachment))
                             ->download($attachment, $variation);
    }

    /**
     * Remove all related files.
     *
     * @param AttachmentContract|Attachment $attachment
     *
     * @return void
     */
    public function unlink(AttachmentContract $attachment)
    {
        foreach ($attachment->variations as $variation => $_) {
            $this->getDisk($attachment)->delete($attachment->getPath($variation));
        }

        $this->getDisk($attachment)->delete($attachment->getPath());
    }

    /**
     * Store file on the disk.
     *
     * @param string $path
     * @param $content
     * @param null|string $visibility
     * @param null|string $disk
     *
     * @return void
     */
    public function put(string $path, $content, $visibility = null, $disk = null)
    {
        $this->manager->disk($disk)->put($path, $content, $visibility);
    }

    /**
     * Store file on the disk with given name.
     *
     * @param string $path
     * @param \Illuminate\Http\UploadedFile $content
     * @param string $filename
     * @param null|string $visibility
     * @param null|string $disk
     *
     * @return void
     */
    public function putAs(string $path, $content, string $filename, $visibility = null, $disk = null)
    {
        $disk = $this->manager->disk($disk);

        if (method_exists($disk, 'putFileAs')) {
            $disk->putFileAs($path, $content, $filename, $visibility);
        } else {
            $disk->put($path.DIRECTORY_SEPARATOR.$filename, $content, $visibility);
        }
    }

    /**
     * @param  AttachmentContract|Attachment $attachment
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter|\Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getDisk(AttachmentContract $attachment)
    {
        return $this->manager->disk($attachment->disk);
    }

    /**
     * Get driver for disk.
     *
     * @param  AttachmentContract|Attachment $attachment
     *
     * @return string
     * @throws \Znck\Attach\Exceptions\InvalidDiskException
     */
    protected function getDriver(AttachmentContract $attachment): string
    {
        $disk = $attachment->disk ?? $this->default;

        if (isset($this->disks[$disk])) {
            return $this->disks[$disk]['driver'];
        }

        throw new InvalidDiskException('Disk ('.$attachment->disk.') is not defined in filesystems.');
    }
}
