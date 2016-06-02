<?php namespace Znck\Attach;

use Throwable;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Contracts\UriSigner;
use Znck\Attach\Contracts\UriGenerator;
use Znck\Attach\Exceptions\FilesystemException;
use Znck\Attach\Exceptions\ManipulationNotFoundException;
use Znck\Attach\Exceptions\StreamNotSupportedException;

trait MediaTrait //extends \Illuminate\Database\Eloquent\Model implements Contracts\Media
{
    
    // FIXME: Should delete file in case of model not saved.
    /**
     * Get URI for media attachment or its manipulation.
     *
     * @param string|null $manipulation Name of the manipulation.
     *
     * @return string
     */
    public function getUri(string $manipulation = null): string {
        return $manipulation
            ? $this->getUriGenerator()->getUrlFor($this, $manipulation)
            : $this->getUriGenerator()->getUri($this);
    }

    /**
     * Get content from media attachment file.
     *
     * @return string
     */
    public function getContent() {
        return $this->getFilesystem()->get($this->getPath());
    }

    /**
     * Get content stream for media attachment file.
     *
     * @return string
     *
     * @throws \Znck\Attach\Exceptions\StreamNotSupportedException
     */
    public function getStream() {
        try {
            return $this->getFilesystem()->getDriver()->readStream($this->getPath());
        } catch (Throwable $e) {
            throw new StreamNotSupportedException(null, null, $e);
        }
    }

    /**
     * Store a media attachment file on disk.
     *
     * @param string|resource $file Content of the media attachment file.
     *
     * @throws FilesystemException
     *
     * @return void
     */
    public function setContent($file) {
        $this->putContent($this->getPath(), $file);
    }

    /**
     * Get HTTP headers for media attachment.
     *
     * @return array
     */
    public function getHttpHeaders(): array {
        return $this->prepareHeadersWith();
    }

    /**
     * Parameter name used for creating URI for the media attachment.
     *
     * @return string
     */
    public function getSecureTokenKey() : string {
        return 'token';
    }

    /**
     * URL signing token for the media.
     *
     * @param null|int $expires Validity of the token.
     *
     * @return string
     */
    public function getSecureToken($expires = null) : string {
        return $this->getEncrypter()->sign($this->getKey(), $expires);
    }

    /**
     * Verify signing token for the media.
     *
     * @param string   $token
     * @param null|int $expires
     *
     * @return bool
     */
    public function verifySecureToken($token, $expires = null) : bool {
        return $this->getEncrypter()->verify($token, $this->getKey(), $expires);
    }


    /**
     * Get visibility of the media attachment.
     *
     * @return string
     */
    public function getVisibility(): string {
        switch ($this->attributes['visibility']) {
            case Media::VISIBILITY_PUBLIC: return 'public';
            default: return 'private';
        }
    }

    /**
     * Set visibility of the media attachment.
     *
     * @param string $visibility
     *
     * @return void
     */
    public function setVisibility(string $visibility) {
        $this->setVisibilityAttribute($visibility);
    }

    /**
     * Get all media attachments of the collection of this media attachment.
     *
     * @return Collection
     */
    public function getCollection(): Collection {
        return new Collection($this->collection, new static());
    }

    /**
     * List of available manipulations.
     *
     * @return array
     */
    public function availableManipulations(): array {
        return array_keys($this->manipulations);
    }

    /**
     * Store a manipulated version of media attachment on disk.
     *
     * @param string          $name Name of manipulation.
     * @param string|resource $file Content of manipulated media attachment file.
     * @param string          $mime Mime type string for the manipulated media attachment.
     *
     * @throws FilesystemException
     *
     * @return bool
     */
    public function setManipulation(string $name, $file, $mime) : bool {
        $path = $this->getPath($name);
        $this->putContent($path, $file);
        $size = $this->getFilesystem()->size($path);

        $this->manipulations = [$name => compact('mime', 'size')] + (array) $this->manipulations;

        return true;
    }

    /**
     * Get HTTP header for manipulated media attachment.
     *
     * @param string $name Name ot the manipulation.
     *
     * @throws ManipulationNotFoundException
     *
     * @return array
     */
    public function getManipulationHeader(string $name) : array {
        if (!array_key_exists($name, $this->manipulations)) {
            throw new ManipulationNotFoundException();
        }

        $mime = $this->manipulations[$name]['mime'];
        $size = $this->manipulations[$name]['size'];

        return $this->prepareHeadersWith($mime, $size);
    }

    /**
     * Get content from manipulated media attachment file.
     *
     * @param string $name Name of the manipulation.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    public function getManipulationContent(string $name) {
        return $this->getFilesystem()->get($this->getPath($name));
    }

    /**
     * Get content stream for manipulated media attachment file.
     *
     * @param string $name Name of the manipulation.
     *
     * @return string
     * 
     * @throws \Znck\Attach\Exceptions\StreamNotSupportedException
     */
    public function getManipulationStream(string $name) {
        try {
            return $this->getFilesystem()->getDriver()->readStream($this->getPath($name));
        } catch (Throwable $e) {
            throw new StreamNotSupportedException(null, null, $e);
        }
    }

    /**
     * Check valid visibility type and add it to attributes.
     *
     * @param string $visibility
     *
     * @return void
     */
    protected function setVisibilityAttribute(string $visibility) {
        $visibilities = [Media::VISIBILITY_PRIVATE, Media::VISIBILITY_PUBLIC, Media::VISIBILITY_SHARED];

        if (in_array($visibility, $visibilities)) {
            $this->attributes['visibility'] = $visibility;
        }
    }

    /**
     * Update `path` attribute and move files after attributes are written in database.
     *
     * @param string $value Path to store the media attachment.
     *
     * @return void
     */
    public function setPathAttribute($value) {
        $old = array_get($this->attributes, 'path');

        if ($old and $this->getFilesystem()->exists($old)) {
            $this->saved(
                function () use ($old, $value) {
                    $this->moveAll($old, $value);
                }
            );
        }

        $this->attributes['path'] = $value;
    }

    /**
     * Get the disk on filesystem.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\League\Flysystem\AdapterInterface
     */
    public function getFilesystem() {
        return app('filesystem')->disk($this->disk);
    }

    /**
     * Get the uri signer.
     *
     * @return UriSigner
     */
    public function getEncrypter() {
        return app(UriSigner::class);
    }

    /**
     * Get the uri generator.
     *
     * @return UriGenerator
     */
    public function getUriGenerator() {
        return app(UriGenerator::class);
    }

    /**
     * Get path for manipulation by name.
     *
     * @param null|string $manipulation Name of manipulation.
     * @param null|string $path         If provided, use this path for computing manipulation path.
     *
     * @return string
     */
    public function getPath($manipulation = null, $path = null) {
        $path = $path ?? $this->path;
        if ($manipulation) {
            $directory = pathinfo($path, PATHINFO_DIRNAME);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = pathinfo($path, PATHINFO_FILENAME);

            return $directory.DIRECTORY_SEPARATOR.$filename.'-'.$manipulation.$extension;
        }

        return $path;
    }

    /**
     * Prepare HTTP response headers for serving the file.
     *
     * @param null|string $mime Mime type string.
     * @param null|int    $size File size in bytes.
     *
     * @return array
     */
    protected function prepareHeadersWith($mime = null, $size = null) {
        return [
                   'Content-Length' => $size ?? $this->size,
                   'Content-Type' => $mime ?? $this->mime,
               ]
               + ($this->getVisibility() === Media::VISIBILITY_PUBLIC ? [] : ['private']);
    }

    /**
     * Store content on disk.
     *
     * @param string          $path
     * @param string|resource $file
     *
     * @throws FilesystemException
     *
     * @return void
     */
    protected function putContent(string $path, $file) {
        if (!$this->getFilesystem()->put($path, $file, $this->getVisibility())) {
            throw new FilesystemException();
        }
    }

    /**
     * Move files on disk.
     *
     * @param string $old from path
     * @param string $new to path
     *
     * @return void
     */
    protected function moveAll($old, $new) {
        $filesystem = $this->getFilesystem();
        if (!$filesystem->exists($this->getPath(null, $old))) {
            return;
        }
        $filesystem->move($this->getPath(null, $old), $this->getPath(null, $new));

        foreach (array_keys($this->manipulations ?? []) as $item) {
            $filesystem->move($this->getPath($item, $old), $this->getPath($item, $new));
        }
    }
}
