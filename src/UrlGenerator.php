<?php

namespace Znck\Attach;

use Znck\Attach\Contracts\AttachmentContract as AttachmentContract;
use Znck\Attach\Contracts\SignerContract;
use Znck\Attach\Contracts\UrlGeneratorContract;

class UrlGenerator implements UrlGeneratorContract
{
    /**
     * @var \Znck\Attach\Contracts\SignerContract
     */
    private $signer;
    /**
     * @var
     */
    private $route;
    /**
     * @var bool
     */
    private $sign;

    /**
     * UrlGenerator constructor.
     *
     * @param \Znck\Attach\Contracts\SignerContract $signer
     * @param $route
     * @param bool $sign
     */
    public function __construct(SignerContract $signer, $route, bool $sign = true)
    {
        $this->signer = $signer;
        $this->route = is_string($route) ? $route : array_get($route, 'as');
        $this->sign = $sign;
    }

    /**
     * Create url from Attachment.
     *
     * @param AttachmentContract|Attachment $attachment
     * @param string|null $variation
     * @param array $params
     * @param bool|null $sign
     *
     * @return string
     */
    public function url(
        AttachmentContract $attachment,
        string $variation = null,
        array $params = [],
        bool $sign = null
    ): string {
        $sign = is_null($sign) ? $this->sign : $sign;

        $filename = $this->prepareFilename($attachment, $variation);

        if ($sign) {
            $expiry = $params['expiry'] ?? null;
            unset($params['expiry']);

            $url = route($this->route, $params + compact('filename'));

            return $this->signer->sign($url, $expiry);
        }

        return route($this->route, $params + compact('filename'));
    }

    /**
     * @param \Znck\Attach\Contracts\AttachmentContract|Attachment $attachment
     * @param string $variation
     *
     * @return string
     */
    protected function prepareFilename(AttachmentContract $attachment, string $variation = null): string
    {
        $basename = $attachment->getAttachmentKey();
        $ext = $attachment->extension;

        if (!$variation) {
            return "${basename}.${ext}";
        }

        return "${basename}.${variation}.${ext}";
    }
}
