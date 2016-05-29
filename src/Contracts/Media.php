<?php namespace Znck\Attach\Contracts;

use Znck\Attach\Collection;
use Znck\Attach\File\File;

/**
 * @property string $mime
 * @property array $manipulations
 * @property array $properties
 * @property string|null $collection Collection or group image belongs to.
 * @property string|null $title User provided name for image.
 * @property string|null $filename Original filename of uploaded file.
 * @property string $disk Files are on this disk.
 * @property string $path Absolute file path on disk.
 * @property int $size Size in bytes.
 * @property int $order Position in collection.
 *
 * @method array toArray()
 */
interface Media
{
    const TYPE_OTHER = -1;
    const TYPE_IMAGE = 1;
    const TYPE_PDF = 2;

    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_SHARED = 'shared';

    /**
     * Type of file.
     *
     * @return int
     */
    public function getType() : int;

    /**
     * URI for the media asset.
     *
     * @param null|string $manipulation
     *
     * @return string
     */
    public function getUri(string $manipulation = null): string;

    /**
     * Instance of original media.
     *
     * @return string
     */
    public function getContent();

    /**
     * Instance of original media.
     *
     * @param mixed $file
     */
    public function setContent($file);

    /**
     * Generate http headers.
     *
     * @return array
     */
    public function getHttpHeaders(): array ;

    /**
     * Secure hash to verify file access authenticity without logging in.
     *
     * @return string
     */
    public function getSecureToken(): string;

    /**
     * Get media visibility public, private or shared.
     *
     * @return string
     */
    public function getVisibility(): string;

    /**
     * Set media visibility.
     *
     * @param string $visibility
     *
     * @return Media
     */
    public function setVisibility(string $visibility);

    /**
     * Get collection this image belongs to.
     *
     * @return Collection
     */
    public function getCollection(): Collection;

    /**
     * Available file manipulations.
     *
     * @return array
     */
    public function availableManipulations(): array;

    /**
     * Verify secure hash.
     *
     * @param string $hash
     *
     * @return bool
     */
    public function verifySecureToken(string $hash): bool;

    /**
     * @param string $name
     * @param mixed $file
     * @param string $mime
     *
     * @return bool
     */
    public function setManipulation(string $name, $file, $mime) : bool;

    /**
     * @param string $name
     *
     * @return array
     */
    public function getManipulationHeader(string $name) : array ;

    /**
     * @param string $name
     *
     * @return string
     */
    public function getManipulationContent(string $name);

    /**
     * @return string
     */
    public function getSecureTokenKey() : string;
}
