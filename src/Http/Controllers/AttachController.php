<?php namespace Znck\Attach\Http\Controllers;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;
use Znck\Attach\Uploaders\DefaultUploader;

class AttachController extends Controller
{
    /**
     * @var Container
     */
    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $id
     *
     * @return \Znck\Attach\Contracts\Media
     */
    protected function getMediaById(string $id)
    {
        return $this->app->make(config('attach.model'))->findOrFail($id);
    }

    public function get(Request $request, $filename, $manipulation = null)
    {
        $media = $this->getMediaById($filename);

        try {
            if (! $media->verifySecureToken($request->input($media->getSecureTokenKey()), $request->input('expires'))) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            abort(401);
        }

        if ($manipulation) {
            return response()->stream(
                function () use ($media, $manipulation) {
                    echo file_get_contents($media->getManipulationStream($manipulation));
                },
                200,
                $media->getManipulationHeader($manipulation)
            );
        }

        return response()->stream(
            function () use ($media) {
                file_get_contents($media->getStream());
            },
            200,
            $media->getHttpHeaders()
        );
    }

    public function upload(DefaultUploader $uploader, Request $request)
    {
        $media = $uploader->upload(
            $request->file('file'),
            $request->only(['properties', 'collection', 'title', 'filename'])
        );

        return response(null, 201, ['Location', $media->getUri()]);
    }

    public function download(Request $request, $filename)
    {
        $media = $this->getMediaById($filename);

        if (! $media->verifySecureToken($request->input($media->getSecureTokenKey()))) {
            abort(401);
        }

        return response(
            $media->getStream(),
            200,
            $media->getHttpHeaders() + ['Content-Disposition' => 'attachment; filename="'.$media->filename.'"']
        );
    }
}
