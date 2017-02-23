<?php namespace Znck\Attach\Util;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;
use Znck\Attach\Contracts\SignerContract;
use Znck\Exceptions\InvalidSignatureException;

class AttachController extends Controller
{
    protected $shouldSign = true;

    /**
     * @var \Znck\Attach\Contracts\AttachmentContract|\Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    public function __construct(AttachmentContract $query)
    {
        $this->shouldSign = config('attach.sign', true);
        $this->query = $query;
    }

    public function serve(Request $request, FinderContract $finder, SignerContract $signer, $filename)
    {
        if ($this->shouldSign) {
            $signature = (string)$request->query('signature');
            $expiry = ((int)$request->query('expiry')) ?: null;

            if (!$signer->verify($request->fullUrl(), $signature, $expiry)) {
                throw new InvalidSignatureException([
                    'signature' => $signature,
                    'expiry' => $expiry,
                    'url' => $request->fullUrl(),
                ], 403, 'Invalid Signature');
            }
        }

        list($attachment, $variation) = $this->findAttachmentByFilename($filename);

        return $finder->download($attachment, $variation);
    }

    protected function findAttachmentByFilename(string $filename)
    {
        if (preg_match('/^(.*)\.([^.]+)\.([^.]+)$/i', $filename, $matches) === 1) {
            $uid = $matches[1];
            $variation = $matches[2];
        } elseif (preg_match('/^(.*)\.([^.]+)$/i', $filename, $matches) === 1) {
            $uid = $matches[1];
            $variation = null;
        } else {
            throw new NotFoundResourceException();
        }

        return [
            $this->query->where($this->query->getAttachmentKeyName(), $uid)->firstOrFail(),
            $variation,
        ];
    }
}
