<?php

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Uploader;
use Znck\Attach\Downloader;
use Znck\Attach\Processors\Resize;
use Znck\Attach\Processors\SaveIfDirty;

if (! function_exists('make_image_uploader')) {
    function make_image_uploader(Request $request, string $key = 'file') : Uploader
    {
        $attachment = app(Attachment::class);
        $uploader = app(Uploader::class, [$request->file($key), $attachment, false]);
        $attachment->fill($request->input());
        (new Resize())->process($attachment);
        (new Resize('thumbnail', 200, 200))->process($attachment);
        (new SaveIfDirty())->process($attachment);

        return $uploader;
    }
}

if (! function_exists('make_file_uploader')) {
    function make_file_uploader(Request $request, string $key = 'file') : Uploader
    {
        $attachment = app(Attachment::class);
        $uploader = app(Uploader::class, [$request->file($key), $attachment]);
        $attachment->fill($request->input());
        (new SaveIfDirty())->process($attachment);

        return $uploader;
    }
}

if (! function_exists('serve_attachment')) {
    function serve_attachment(string $filename) : Response
    {
        return (new Downloader())->response($filename);
    }
}

if (! function_exists('download_attachment')) {
    function download_attachment(string $filename) : Response
    {
        return (new Downloader())->download($filename);
    }
}

if (! function_exists('sign_url')) {
    function sign_url(string $url, $extras = [], int $expiry = null)
    {
        ksort($extras);

        $expiry = is_null($expiry) ? config('attach.signing.expiry') : $expiry;
        $expiry = $expiry == 0 ? 0 : time() * $expiry * 60;

        $secret = config('attach.signing.key');

        $extras_str = '';
        foreach ($extras as $key => $value) {
            $extras_str .= "${key}:${value}";
        }
        $signature = md5("${url}::${extras_str}::${expiry}::${secret}");

        return url($url, compact('expiry', 'signature'));
    }
}

if (! function_exists('verify_url_signature')) {
    function verify_url_signature(string $url, string $signature, $expiry = 0)
    {
        $secret = config('attach.signing.key');

        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $extras);
        $extras_str = '';
        foreach ($extras as $key => $value) {
            $extras_str .= "${key}:${value}";
        }
        $url = substr($url, 0, strpos($url, $query) - 1);

        $verify = md5("${url}::${extras_str}::${expiry}::${secret}");

        return hash_equals($signature, $verify);
    }
}

if (! function_exists('attach_url')) {
    function attach_url(Attachment $attachment, string $var = null, $params = [], bool $sign = null)
    {
        $route = config('attach.route');
        $sign = is_null($sign) ? config('attach.sign') : $sign;
        $name = is_string($route) ? $route : array_get($route, 'as');
        $filename = $attachment->getAttachmentKey().(is_null($var) ? '' : '.'.$var).'.'.$attachment->extension;
        $url = route($name, $params + compact('filename'));

        if ($sign) {
            return sign_url($url);
        }

        return $url;
    }
}
