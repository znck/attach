<?php namespace Znck\Attach\TokenGenerators;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Hashing\BcryptHasher;
use Znck\Attach\Contracts\TokenGenerator;

class EncryptedHash implements TokenGenerator
{
    /**
     * @var Guard
     */
    protected $guard;
    /**
     * @var BcryptHasher
     */
    private $hasher;


    public function __construct(Guard $guard, BcryptHasher $hasher)
    {
        $this->guard = $guard;
        $this->hasher = $hasher;
    }

    public function make(string $id)
    {
        return base64_encode($this->hasher->make($this->guard->id().$id));
    }

    public function verify(string $hash, string $id)
    {
        return $this->hasher->check($this->guard->id().$id, base64_decode($hash));
    }
}
