<?php namespace Znck\Attach\Contracts;

use Illuminate\Contracts\Filesystem\Filesystem;

interface Finder
{
    public function getStorage() : Filesystem;

    public function setStorage(Filesystem $storage);

    public function get(string $path);

    public function put(string $path, $content, $visibility = null);

    public function size(string $path);

    public function getPath(Attachment $attachment, string $variation) : string;

    /**
     * @param string $path
     *
     * @return resource
     */
    public function readStream(string $path);

    public function useDisk(string $disk) : self;
}
