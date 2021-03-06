<?php

use Znck\Attach\Contracts\AttachmentContract;

if (!function_exists('attach_url')) {
    /**
     * Generate url for attachment.
     *
     * @param AttachmentContract $attachment
     * @param string|null $var
     * @param array $params
     * @param bool|null $sign
     *
     * @return null|string
     */
    function attach_url($attachment, string $var = null, $params = [], bool $sign = null)
    {
        if (!($attachment instanceof AttachmentContract)) {
            return null;
        }

        return app(Znck\Attach\Contracts\UrlGeneratorContract::class)->url($attachment, $var, $params, $sign);
    }
}
