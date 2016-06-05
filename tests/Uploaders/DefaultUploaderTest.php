<?php namespace Test\Znck\Attach\Uploaders;

use Illuminate\Http\UploadedFile;
use Test\Znck\Attach\TestCase;
use Znck\Attach\Attachment;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Uploaders\DefaultUploader;

class DefaultUploaderTest extends TestCase
{
    public function test_upload()
    {
        $uploader = new DefaultUploader();

        $file = new UploadedFile(__DIR__.DIRECTORY_SEPARATOR.'uploaded.txt', 'uploaded.txt');

        /*
         * @var Attachment
         */
        $media = $uploader->upload($file);
        $this->assertTrue($media->exists);
        $this->seeInDatabase('attachments', []);

        $this->assertTrue($media->getFilesystem()->exists($media->getPath()), 'File '.$media->getPath().' not found.');

        $media = $uploader->upload($file, ['path' => 'bar.txt', 'visibility' => Media::VISIBILITY_PUBLIC]);
        $this->assertTrue($media->exists);
        $this->seeInDatabase('attachments', ['visibility' => Media::VISIBILITY_PUBLIC]);
        $this->assertTrue($media->getFilesystem()->exists('bar.txt'));
    }
}
