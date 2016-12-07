<?php

namespace Znck\Attach\Util;

use Znck\Attach\Contracts\Signer as SignerInterface;

class Signer implements SignerInterface
{
    /**
     * Create a signed url
     *
     * @param  string $url          Given url.
     * @param  int|null $expiry     Expired at timestamp.
     * @param  bool $ignoreParams   Ignore params.
     *
     * @return string               Signed url.
     */
    public function sign(string $url, int $expiry = null, bool $ignoreParams = true): string {
        $originalUrl = $url;

        if ($ignoreParams === true) {
            $url = $this->getUrl($url);
        } else {
            $url = url($this->getUrl($url), $this->getParameters($url));
        }

        $secret = config('attach.signing.key');
        $source = is_null($expiry) ? "${url}::${secret}" : "${url}::${expiry}::${secret}";
        $signature = md5($source);

        return url($originalUrl, is_null($expiry) ? compact('signature') : compact('expiry', 'signature'));
    }

    /**
     * Verify url signature
     *
     * @param  string $url              Signed url.
     * @param  string $signature        Given signature.
     * @param  int|null $expiry         Expired at timestamp.
     * @param  bool|array $ignoreParams Ignore params.
     *
     * @return bool                     True if valid.
     */
    public function verify(string $url, string $signature, int $expiry = null, $ignoreParams = true): bool {
        $params = $this->getParameters($url);
        $url = $this->getUrl($url);

        $expiry = array_get($params, 'expiry', null);
        $signature = (string) array_get($params, 'signature', '');

        unset($params['expiry']);
        unset($params['signature']);

        if (is_array($ignoreParams)) {
            $params = array_except($params, $ignoreParams);
        }

        if ($ignoreParams !== true) {
            $url = url($url, $params);
        }

        $secret = config('attach.signing.key');
        $source = is_null($expiry) ? "${url}::${secret}" : "${url}::${expiry}::${secret}";
        $expected = md5($source);

        return hash_equals($expected, $signature);
    }

    /**
     * Get query parameters from url.
     *
     * @param  string $url Given url string.
     *
     * @return array       Key sorted list of parameters.
     */
    protected function getParameters(string $url): array {
        $query = parse_url($url, PHP_URL_QUERY);

        parse_str($query, $params);

        ksort($params);

        return $params;
    }

    /**
     * Get url without query string.
     *
     * @param  string $url Given url.
     *
     * @return string      Stripped url.
     */
    protected function getUrl(string $url): string {
        return array_first(explode('?', $url, 2));
    }
}
