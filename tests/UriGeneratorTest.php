<?php namespace Test\Znck\Attach;

use Znck\Attach\Attachment;
use Znck\Attach\UriGenerator;

class UriGeneratorTest extends TestCase
{
    public function test_get_uri()
    {
        $this->app['router']->get('/{filename}/{manipulation?}', ['as' => 'media', 'uses' => 'Foo@foo']);

        $media = Attachment::create([
            'filename'   => 'foo.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
        ]);

        $generator = new UriGenerator($this->app['url'], 'media', ['id' => 'filename']);
        $this->assertEquals('http://localhost/'.$media->getKey(),
            explode('?', $generator->getUri($media), 2)[0]);

        $generator = new UriGenerator($this->app['url'], 'media', ['id' => 'filename', 'manipulation' => 'manipulation']);
        $this->assertEquals('http://localhost/'.$media->getKey().'/foo',
            explode('?', $generator->getUrlFor($media, 'foo'), 2)[0]);
    }
}
