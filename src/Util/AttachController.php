<?php namespace Znck\Attach\Util;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AttachController extends Controller
{
    public function serve(Request $request, $filename)
    {
        if (config('attach.sign')) {
            $url = url($request->url(), array_except($request->query(), ['signature', 'expire']));
            $signature = $request->query('signature', '');
            $expiry = $request->query('expiry', 0);
            if (! verify_url_signature($url, $signature, $expiry)) {
                return abort(404);
            }
        }

        return serve_attachment($filename);
    }
}
