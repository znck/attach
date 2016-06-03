<?php namespace Znck\Attach\UriSigners;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Hashing\BcryptHasher;
use Znck\Attach\Contracts\UriSigner;
use Znck\Attach\Exceptions\TokenExpired;
use Znck\Attach\Exceptions\TokenInvalid;

class EncryptedHash implements UriSigner
{
    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @var BcryptHasher
     */
    protected $hasher;

    public function __construct(Guard $guard, BcryptHasher $hasher)
    {
        $this->guard = $guard;
        $this->hasher = $hasher;
    }

    public function sign(string $id, $expires = null)
    {
        return urlencode(base64_encode($this->hasher->make($this->guard->id().$id.$expires)));
    }

    public function verify(string $hash, string $id, $expires)
    {
        if (! $hash or ! $this->hasher->check($this->guard->id().$id.$expires, base64_decode(urldecode($hash)))) {
            throw new TokenInvalid();
        }

        if ($expires and Carbon::createFromTimestamp($expires)->lt(Carbon::now())) {
            throw new TokenExpired();
        }

        return true;
    }
}
