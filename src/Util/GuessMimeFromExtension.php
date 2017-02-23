<?php namespace Znck\Attach\Util;

use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

class GuessMimeFromExtension implements MimeTypeGuesserInterface
{
    const APACHE_MIMES = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Guesses the mime type of the file with the given path.
     *
     * @param string $path The path to the file
     *
     * @throws FileNotFoundException If the file does not exist
     * @throws AccessDeniedException If the file could not be read
     *
     * @return string The mime type or NULL, if none could be guessed
     */
    public function guess($path)
    {
        return $this->getMimes()->get($this->getExtension($path));
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getMimes()
    {
        return $this->cache->remember('vendor:znck/trust.mimes', 1440, function () {
            return collect($this->generateUpToDateMimeArray());
        });
    }

    protected function generateUpToDateMimeArray()
    {
        $types = [];
        $lines = array_filter(explode("\n", file_get_contents(self::APACHE_MIMES)), function ($line) {
            return ! starts_with($line, '#');
        });

        foreach ($lines as $line) {
            if (count($extensions = explode(' ', $line)) >= 2) {
                $mime = trim(array_shift($extensions));

                foreach ($extensions as $extension) {
                    $types[trim($extension)] = $mime;
                }
            }
        }

        return $types;
    }

    protected function getExtension(string $path)
    {
        $parts = explode('.', $path);

        return array_pop($parts);
    }
}
