<?php namespace Znck\Attach\Contracts;

interface UrlGeneratorContract
{
    /**
     * Create url from Attachment.
     *
     * @param AttachmentContract $attachment
     * @param string|null        $variation
     * @param array              $params
     * @param bool|null          $sign
     *
     * @return string
     */
    public function url(
        AttachmentContract $attachment,
        string $variation = null,
        array $params = [],
        bool $sign = null
    ): string;
}
