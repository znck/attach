<?php namespace Znck\Attach\Contracts;

interface FinderContract extends Downloader
{
    /**
     * Get original file contents.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract $attachment
     * @param string $variation
     *
     * @return string
     */
    public function get(AttachmentContract $attachment, string $variation = null);

    /**
     * Remove all related files.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract $attachment
     *
     * @return void
     */
    public function unlink(AttachmentContract $attachment);

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
    public function put(string $path, $content, $visibility = null, $disk = null);

    /**
     * Store file on the disk with given name.
     *
     * @param string $path
     * @param $content
     * @param string $filename
     * @param null|string $visibility
     * @param null|string $disk
     *
     * @return void
     */
    public function putAs(string $path, $content, string $filename, $visibility = null, $disk = null);
}
