<?php namespace Znck\Attach\Contracts;

use Znck\Attach\Collection;

/**
 * @property string      $mime
 * @property array       $manipulations
 * @property array       $properties
 * @property string|null $collection Collection or group image belongs to.
 * @property string|null $title      User provided name for image.
 * @property string|null $filename   Original filename of uploaded file.
 * @property string      $disk       Files are on this disk.
 * @property string      $path       Absolute file path on disk.
 * @property int         $size       Size in bytes.
 * @property int         $order      Position in collection.
 *
 * @method array toArray()
 */
interface Media
{
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_SHARED = 'shared';

    /**
     * Get URI for media attachment or its manipulation.
     *
     * @param string|null $manipulation Name of the manipulation.
     *
     * @return string
     */
    public function getUri(string $manipulation = null): string;

    /**
     * Get content from media attachment file.
     *
     * @return string
     */
    public function getContent();

    /**
     * Get content stream for media attachment file.
     *
     * @return string
     */
    public function getStream();

    /**
     * Store a media attachment file on disk.
     *
     * @param string|resource $file Content of the media attachment file.
     *
     * @return void
     */
    public function setContent($file);

    /**
     * Get HTTP headers for media attachment.
     *
     * @return array
     */
    public function getHttpHeaders(): array;

    /**
     * Parameter name used for creating URI for the media attachment.
     *
     * @return string
     */
    public function getSecureTokenKey() : string;

    /**
     * URL signing token for the media.
     *
     * @param null|int $expires Validity of the token.
     *
     * @return string
     */
    public function getSecureToken($expires = null) : string;

    /**
     * Verify signing token for the media.
     *
     * @param string   $token
     * @param null|int $expires
     *
     * @return bool
     */
    public function verifySecureToken($token, $expires = null) : bool;

    /**
     * Get visibility of the media attachment.
     *
     * @return string
     */
    public function getVisibility(): string;

    /**
     * Set visibility of the media attachment.
     *
     * @param string $visibility
     *
     * @return void
     */
    public function setVisibility(string $visibility);

    /**
     * Get all media attachments of the collection of this media attachment.
     *
     * @return Collection
     */
    public function getCollection(): Collection;

    /**
     * List of available manipulations.
     *
     * @return array
     */
    public function availableManipulations(): array;

    /**
     * Store a manipulated version of media attachment on disk.
     *
     * @param string          $name Name of manipulation.
     * @param string|resource $file Content of manipulated media attachment file.
     * @param string          $mime Mime type string for the manipulated media attachment.
     *
     * @return bool
     */
    public function setManipulation(string $name, $file, $mime) : bool;

    /**
     * Get HTTP header for manipulated media attachment.
     *
     * @param string $name Name ot the manipulation.
     *
     * @return array
     */
    public function getManipulationHeader(string $name) : array;

    /**
     * Get content from manipulated media attachment file.
     *
     * @param string $name Name of the manipulation.
     *
     * @return string
     */
    public function getManipulationContent(string $name);

    /**
     * Get content stream for manipulated media attachment file.
     *
     * @param string $name Name of the manipulation.
     *
     * @return string
     */
    public function getManipulationStream(string $name);
}
