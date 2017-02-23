<?php namespace Znck\Attach;

use Illuminate\Filesystem\FilesystemManager;
use Znck\Attach\Adaptors\LocalDiskDownloader;
use Znck\Attach\Contracts\Downloader;

class DownloaderFactory
{
    protected $adaptors = [];

    public function adaptor(FilesystemManager $manager, string $driver): Downloader
    {
        return isset($this->adaptors[$driver]) ? $this->adaptors[$driver] : ($this->adaptors[$driver] = $this->$driver($manager));
    }

    public function local(FilesystemManager $manager)
    {
        return new LocalDiskDownloader($manager);
    }
}
