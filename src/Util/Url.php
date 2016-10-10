<?php

namespace Znck\Attach\Util;

use Znck\Attach\Contracts\Signer;
use Znck\Attach\Contracts\UrlGenerator;

class Url implements UrlGenerator {
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
    public function url(Attachment $attachment, string $variation = null, $params = [], bool $sign = null): string {
        $route = config('attach.route');
        $sign = is_null($sign) ? config('attach.sign', true) : $sign;
        $routeName = is_string($route) ? $route : array_get($route, 'as');
        $filename = $attachment->getAttachmentKey().(is_null($var) ? '' : '.'.$var).'.'.$attachment->extension;

        if ($sign) {
            $expiry = array_get($params, 'expiry');
            unset($params['expiry']);
            $url = route($name, $params + compact('filename'));

            return app(Signer::class)->sign($url);
        }

        return route($name, $params + compact('filename'));
    }
}
