<?php namespace Znck\Attach\Contracts;

interface UriSigner
{
    public function sign(string $id, $expires = null);

    public function verify(string $hash, string $id, $expires);
}
