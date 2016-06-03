<?php namespace Test\Znck\Attach\Http\Controllers;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Http\Controllers\AttachController;
use Znck\Attach\Manipulators\Thumb;
use Znck\Attach\Uploaders\DefaultUploader;

class AttachControllerTest extends TestCase
{
    use MakesHttpRequests;

    public function test_get() {
        $this->app['router']->get('/{filename}/{manipulation?}', ['as' => 'media', 'uses' => AttachController::class.'@get']);

        $uploader = new DefaultUploader();
        $media = $uploader->upload(new UploadedFile(__DIR__.'/../../Manipulators/anon.jpg', 'anon.jpg'));

        $this->get($media->getUri())
             ->seeHeader('Content-Length', $media->size)
             ->seeHeader('Content-Type', $media->mime);

        $thumb = new Thumb($this->app);
        $thumb->apply($media);
        $media->save();

        $this->get($media->getUri('thumb'))
             ->seeHeader('Content-Length', $media->manipulations['thumb']['size'])
             ->seeHeader('Content-Type', $media->manipulations['thumb']['mime']);

        $this->expectException(HttpException::class);
        $this->get(explode('?', $media->getUri('thumb'))[0])
             ->seeStatusCode(401);
    }
}
