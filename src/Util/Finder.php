<?php namespace Znck\Attach\Util;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Finder as FinderInterface;

class Finder implements FinderInterface
{
    protected $storage;

    public function getStorage() : Filesystem {
        if (is_null($this->storage)) {
            $this->storage = app(Storage::class);
        }

        return $this->storage;
    }

    public function setStorage(Filesystem $storage) {
        $this->storage = $storage;
    }

    public function get(string $path) {
        return $this->getStorage()->get($path);
    }

    public function put(string $path, $content, $visibility = null) {
        return $this->getStorage()->put($path, $content, $visibility);
    }

    /**
     * @param string $path
     *
     * @return resource|false
     */
    public function readStream(string $path) {
        return $this->getStorageDriver()->readStream($path);
    }

    public function useDisk(string $disk = null) : FinderInterface {
        $this->setStorage(app(FilesystemManager::class)->disk($disk));

        return $this;
    }


    public function getStorageDriver() : FilesystemInterface {
        return $this->getStorage()->getDriver();
    }

    public function size(string $path) {
        return $this->getStorage()->size($path);
    }

    public function getPath(Attachment $attachment, string $variation) : string {
        $path = $attachment->path;
        $extension = $attachment->extension;
        $path = str_replace_last($extension, '', $path);

        return $path.$variation.'.'.$extension;
    }
}
