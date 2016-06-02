<?php namespace Test\Znck\Attach\Manipulators;

use Illuminate\Http\UploadedFile;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Manipulators\Move;
use Znck\Attach\Uploaders\DefaultUploader;

class MoveTest extends TestCase
{

    public function test_all() {
        $uploader = new DefaultUploader();
        $media = $uploader->upload(new UploadedFile(__DIR__.DIRECTORY_SEPARATOR.'anon.jpg', 'anon.jpg'));


        $move = new Move();
        $move->path = 'bar.jpg';

        $move->apply($media);
        $media->save();

        $this->assertEquals('bar.jpg', $media->path);
        $this->assertTrue($media->getFilesystem()->exists('bar.jpg'));

        $move->setAttributes([]);
        $move->apply($media);
        $this->assertEquals('bar.jpg', $media->path);
    }
}
