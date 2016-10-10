<?php namespace Znck\Attach\Contracts;

interface UrlGenerator {
    /**
     * Create url from Attachment.
     *
     * @param  Attachment   $attachment
     * @param  string|null  $variation
     * @param  array        $params
     * @param  bool|null    $sign
     * 
     * @return string
     */
    public function url(Attachment $attachment, string $variation = null, $params = [], bool $sign = null): string;
}
