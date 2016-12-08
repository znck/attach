<?php namespace Znck\Attach\Util;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Znck\Attach\Contracts\Signer;

class AttachController extends Controller
{
    public function serve(Request $request, Signer $signer, $filename)
    {
        if (config('attach.sign')) {
            $url = url($request->url(), array_except($request->query(), ['signature', 'expire']));
            $signature = $request->query('signature');
            $expiry = $request->query('expiry');
            if (! $signer->verify($url, $signature, $expiry)) {
                return abort(404);
            }
        }

        return serve_attachment($filename);
    }
}
