<?php namespace Test\Znck\Attach\Manipulators;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageServiceProviderLaravel5;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Manipulators\Resize;
use Znck\Attach\Uploaders\DefaultUploader;

class ResizeTest extends TestCase
{
    protected function getRequiredServiceProviders($app) {
        return [ImageServiceProviderLaravel5::class];
    }


    public function test_all() {
        $resize = new Resize($this->app['image']);
        $resize->setAttributes([]);
        $resize->name = 'small';
        $resize->width = 600;

        $uploader = new DefaultUploader();
        $media = $uploader->upload(new UploadedFile(__DIR__.DIRECTORY_SEPARATOR.'anon.jpg', 'anon.jpg'));

        $this->assertTrue($media->exists);
        $resize->apply($media);
        $media->save();

        $this->assertTrue($media->getFilesystem()->exists($media->getPath('small')));
        $this->assertArrayHasKey('small', $media->manipulations);
        $this->assertEquals(600, $this->app['image']->make($media->getManipulationContent('small'))->width());
    }
}
