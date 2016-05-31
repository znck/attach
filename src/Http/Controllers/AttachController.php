<?php namespace Znck\Attach\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

        if (! $media->verifySecureToken($request->input($media->getSecureTokenKey()))) {
            abort(401);
        }

        if ($manipulation) {
            return response(
                $media->getManipulationContent($manipulation),
                200,
                $media->getManipulationHeader($manipulation)
            );
        }

        return response($media->getContent(), 200, $media->getHttpHeaders());
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

        return response($media->getContent(), 200, $media->getHttpHeaders() + ['Content-Disposition' => 'attachment; filename="'.$media->filename.'"']);
    }
}
