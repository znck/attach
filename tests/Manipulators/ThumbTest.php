<?php namespace Test\Znck\Attach\Manipulators;

use Illuminate\Http\UploadedFile;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Manipulators\Thumb;
use Znck\Attach\Uploaders\DefaultUploader;

class ThumbTest extends TestCase
{

    public function test_all() {
        $thumb = new Thumb($this->app);

        $uploader = new DefaultUploader();

        $media = $uploader->upload(new UploadedFile(__DIR__.DIRECTORY_SEPARATOR.'anon.jpg', 'anon.jpg'));

        $this->assertTrue($media->exists);

        $thumb->apply($media);

        $media->save();

        $this->assertArrayHasKey('thumb', $media->manipulations);
        $this->assertTrue($media->getFilesystem()->exists($media->getPath('thumb')));
    }
}
