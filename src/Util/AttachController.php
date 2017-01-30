<?php namespace Znck\Attach\Util;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Znck\Attach\Contracts\Signer;
use Znck\Exceptions\InvalidSignatureException;

class AttachController extends Controller
{
    public function serve(Request $request, Signer $signer, $filename)
    {
        if (config('attach.sign')) {
            $url = url($request->url(), array_except($request->query(), ['signature', 'expire']));
            $signature = (string) $request->query('signature');
            $expiry = $request->query('expiry');
            if (! $signer->verify($url, $signature, $expiry)) {
                throw new InvalidSignatureException([
                    'signature' => $signature,
                    'expiry' => $expiry,
                    'url' => $url,
                ], 403, 'Invalid Signature');
            }
        }

        return serve_attachment($filename);
    }
}
