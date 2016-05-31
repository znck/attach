<?php namespace Test\Znck\Attach;

use Exception;
use Illuminate\Contracts\Queue\Queue;
use InvalidArgumentException;
use Znck\Attach\Attachment;
use Znck\Attach\Contracts\Manipulation;
use Znck\Attach\Exceptions\ManipulationFailedException;
use Znck\Attach\Exceptions\ManipulationNotFoundException;
use Znck\Attach\Manager;

class ManagerTest extends TestCase
{
    public function test_available()
    {
        $manager = new Manager($this->app);

        $manipulations = $manager->available();

        $this->assertInArray('Move', $manipulations);
        $this->assertInArray('Resize', $manipulations);
        $this->assertCount(count(glob(__DIR__.'/../src/Manipulators/*.php')), $manipulations);
    }

    public function test_applied()
    {
        $manager = new Manager($this->app);

        $this->assertTrue(is_array($manager->applied()));
        $this->assertCount(0, $manager->applied());

        $manager->add('Resize');

        $this->assertInArray('Resize', $manager->applied());
        $this->assertCount(1, $manager->applied());
    }

    public function test_add_custom()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)->setMethods(
            ['setAttributes', 'getName', 'apply']
        )->getMockForAbstractClass();

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->with(['foo' => 'bar'])
            ->willReturn($manipulator);

        $this->app->instance('Foo', $manipulator);

        $manager->add('Foo', ['foo' => 'bar']);
    }

    public function test_add_fail()
    {
        $manager = new Manager($this->app);
        $this->expectException(ManipulationNotFoundException::class);
        $manager->add('FooBarBarBoom');
    }

    public function test_run_with_collection()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)
            ->setMethods(['setAttributes', 'apply'])
            ->getMockForAbstractClass();

        $media = Attachment::create([
            'filename'   => 'foo.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
            'collection' => 'baz',
        ]);
        Attachment::create([
            'filename'   => 'bar.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
            'collection' => 'baz',
        ]);

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->willReturn($manipulator);

        $manipulator->expects($this->exactly(2))
            ->method('apply');

        $this->app->instance('Foo', $manipulator);

        $manager->add('Foo');

        $manager->run($media->getCollection());
    }

    public function test_run_with_media()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)
            ->setMethods(['setAttributes', 'apply'])
            ->getMockForAbstractClass();

        $media = Attachment::create([
            'filename'   => 'foo.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
        ]);
        $this->assertTrue($media->exists);

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->willReturn($manipulator);

        $manipulator->expects($this->once())
            ->method('apply')
            ->with($media);

        $this->app->instance('Foo', $manipulator);

        $manager->add('Foo');

        $manager->run($media);
    }

    public function test_run_with_anything()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)
            ->setMethods(['setAttributes', 'apply'])
            ->getMockForAbstractClass();

        $this->app->instance('Foo', $manipulator);

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->willReturn($manipulator);

        $manager->add('Foo');
        $this->expectException(InvalidArgumentException::class);
        $manager->run(null);
    }

    public function test_run_with_failing_manipulator()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)
            ->setMethods(['setAttributes', 'apply'])
            ->getMockForAbstractClass();

        $this->app->instance('Foo', $manipulator);

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->willReturn($manipulator);

        $manipulator->expects($this->once())
            ->method('apply')
            ->willThrowException(new Exception());

        $manager->add('Foo');
        $this->expectException(ManipulationFailedException::class);
        $manager->run(Attachment::create([
            'filename'   => 'foo.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
        ]));
    }

    public function test_run_with_failing_manipulator_check_exception()
    {
        $manager = new Manager($this->app);
        $manipulator = $this->getMockBuilder(Manipulation::class)
            ->setMethods(['setAttributes', 'apply'])
            ->getMockForAbstractClass();

        $this->app->instance('Foo', $manipulator);

        $manipulator->expects($this->once())
            ->method('setAttributes')
            ->willReturn($manipulator);

        $manipulator->expects($this->once())
            ->method('apply')
            ->willThrowException(new ThrowableException());

        $manager->add('Foo');
        try {
            $manager->run(Attachment::create([
                'filename'   => 'foo.jpg',
                'path'       => '',
                'mime'       => 'image/jpeg',
                'size'       => 100,
                'visibility' => 'public',
            ]));
        } catch (ManipulationFailedException $e) {
            $this->assertCount(1, $e->getManipulations());
        }
    }

    public function test_run_on_queue()
    {
        $queue = $this->getMockBuilder(Queue::class)
            ->setMethods(['push'])
            ->getMockForAbstractClass();
        $this->app->instance('queue', $queue);
        $manager = new Manager($this->app);

        $queue->expects($this->once())
            ->method('push')
            ->with($manager)
            ->willReturn(true);

        $manager->runOnQueue(Attachment::create([
            'filename'   => 'foo.jpg',
            'path'       => '',
            'mime'       => 'image/jpeg',
            'size'       => 100,
            'visibility' => 'public',
        ]));
    }
}
