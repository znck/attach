<?php namespace Test\Znck\Attach\UriSigners;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Hashing\BcryptHasher;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Attachment;
use Znck\Attach\Exceptions\TokenExpired;
use Znck\Attach\Exceptions\TokenInvalid;
use Znck\Attach\UriSigners\EncryptedHash;

class EncryptedHashTest extends TestCase
{
    /**
     * @var Attachment
     */
    protected $media;

    public function test_all()
    {
        $signer = $this->prepareForTests();

        $token = $signer->sign($this->media->getKey());
        $this->assertTrue($signer->verify($token, $this->media->getKey(), null));

        $expires = Carbon::now()->addDays(2)->timestamp;
        $token = $signer->sign($this->media->getKey(), $expires);
        $this->assertTrue($signer->verify($token, $this->media->getKey(), $expires));
    }

    /**
     * @return EncryptedHash
     */
    private function prepareForTests()
    {
        /** @var Guard $user */
        $user = $this->getMockBuilder(Guard::class)
            ->setMethods(['id'])
            ->getMockForAbstractClass();

        $signer = new EncryptedHash($user, new BcryptHasher());

        $this->media = Attachment::create(
            [
                'filename'   => 'foo.jpg',
                'path'       => '',
                'mime'       => 'image/jpeg',
                'size'       => 100,
                'visibility' => 'public',
            ]
        );

        return $signer;
    }

    public function test_expired()
    {
        $signer = $this->prepareForTests();

        $expires = Carbon::now()->addDays(-2)->timestamp;
        $token = $signer->sign($this->media->getKey(), $expires);
        $this->expectException(TokenExpired::class);
        $signer->verify($token, $this->media->getKey(), $expires);
    }

    public function test_invalid()
    {
        $signer = $this->prepareForTests();

        $token = $signer->sign(0);
        $this->expectException(TokenInvalid::class);
        $signer->verify($token, $this->media->getKey(), null);
    }

    public function test_malformed()
    {
        $signer = $this->prepareForTests();

        $this->expectException(TokenInvalid::class);
        $signer->verify(base64_encode('foo'), $this->media->getKey(), null);
    }
}
