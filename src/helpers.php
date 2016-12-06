<?php

if (!function_exists('serve_attachment')) {
    function serve_attachment(string $filename) {
        return app(new Znck\Attach\Contracts\Downloader::class)->response($filename);
    }
}

if (!function_exists('download_attachment')) {
    function download_attachment(string $filename) {
        return app(new Znck\Attach\Contracts\Downloader::class)->download($filename);
    }
}

if (!function_exists('attach_url')) {
    /**
     * @param Attachment $attachment
     * @param string|null $var
     * @param array $params
     * @param bool|null $sign
     *
     * @return null|string
     */
    function attach_url($attachment, string $var = null, $params = [], bool $sign = null) {
        if ($attachment instanceof Znck\Attach\Contracts\Attachment)
            return app(Znck\Attach\Contracts\UrlGenerator::class)->url($attachment, $var, $params, $sign);

        return null;
    }
}
