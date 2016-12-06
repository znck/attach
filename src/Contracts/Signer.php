<?php

namespace Znck\Attach\Contracts;

interface Signer
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
    public function sign(string $url, int $expiry = null, bool $ignoreParams = true): string;

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
    public function verify(string $url, string $signature, int $expiry = null, $ignoreParams = true): bool;
}
