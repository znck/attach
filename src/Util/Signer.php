<?php

namespace Znck\Attach\Util;

use Znck\Attach\Contracts\Signer as SignerInterface;

class Signer implements SignerInterface
{
    protected $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }
    /**
     * Create a signed url.
     *
     * @param string   $url          Given url.
     * @param int|null $expiry       Expired at timestamp.
     * @param bool|array $ignoreParams Ignore params.
     *
     * @return string Signed url.
     */
    public function sign(string $url, int $expiry = null, $ignoreParams = true): string
    {
        $originalUrl = $url;

        if ($ignoreParams === true) {
            $url = $this->getUrl($url);
        } else {
            $url = $this->url($this->getUrl($url), array_except($this->getParameters($url), (array) $ignoreParams));
        }

        $source = is_null($expiry) ? $url : "${url}::${expiry}";
        $signature = hash_hmac('sha256', $source, $this->secret);


        return $this->url($originalUrl, is_null($expiry) ? compact('signature') : compact('expiry', 'signature'));
    }

    public function url($url, $params) {
        $separator =  (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';

        return $url . $separator . http_build_query($params);
    }

    /**
     * Verify url signature.
     *
     * @param string     $url          Signed url.
     * @param string     $signature    Given signature.
     * @param int|null   $expiry       Expired at timestamp.
     * @param bool|array $ignoreParams Ignore params.
     *
     * @return bool True if valid.
     */
    public function verify(string $url, string $signature, int $expiry = null, $ignoreParams = true): bool
    {
        $params = $this->getParameters($url);
        $url = $this->getUrl($url);

        unset($params['expiry']);
        unset($params['signature']);

        if (is_array($ignoreParams)) {
            $params = array_except($params, $ignoreParams);
        }

        if ($ignoreParams !== true) {
            $url = $this->url($url, $params);
        }

        $source = is_null($expiry) ? $url : "${url}::${expiry}";
        $expected = hash_hmac('sha256', $source, $this->secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Get query parameters from url.
     *
     * @param string $url Given url string.
     *
     * @return array Key sorted list of parameters.
     */
    protected function getParameters(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);

        parse_str($query, $params);

        ksort($params);

        return $params;
    }

    /**
     * Get url without query string.
     *
     * @param string $url Given url.
     *
     * @return string Stripped url.
     */
    protected function getUrl(string $url): string
    {
        return array_first(explode('?', $url, 2));
    }
}
