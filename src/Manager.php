<?php namespace Znck\Attach;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Znck\Attach\Contracts\Manipulation;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Exceptions\ManipulationFailedException;

class Manager implements Contracts\Manager
{
    use Queueable, SerializesModels, InteractsWithQueue;
    /**
     * @var \Illuminate\Contracts\Queue\Queue
     */
    protected $queue;

    /**
     * @var Manipulation[]
     */
    protected $manipulators = [];

    /**
     * @var Media|Collection
     */
    protected $media;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->queue = $this->container->make('queue');
    }

    public function available() : array
    {
        $glob = glob(__DIR__.'/Manipulators/*.php');

        if ($glob === false) {
            return [];
        }

        return array_map(function ($file) {
            return explode('.', basename($file), 2)[0];
        }, $glob);
    }

    public function applied() : array
    {
        return array_map(function ($item) {
            return get_class($item);
        }, $this->manipulators);
    }

    /**
     * @param string $name
     *
     * @return Manipulation
     */
    protected function getManipulator(string $name)
    {
        return $this->container->make('\\Znck\\Attach\\Manipulations\\'.Str::studly($name));
    }

    public function handle()
    {
        if ($this->media instanceof Collection) {
            $this->media->each([$this, 'runManipulations']);
        } elseif ($this->media instanceof Media) {
            $this->runManipulations($this->media);
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function add(string $name, array $attributes = []) : self
    {
        $this->manipulators[] = $this->getManipulator($name)->setAttributes($attributes);
    }

    public function run($media)
    {
        $this->handle();
    }

    public function runOnQueue($media)
    {
        $this->media = $media;
        $this->queue->push($this);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|Media $media
     *
     * @throws ManipulationFailedException
     */
    protected function runManipulations($media)
    {
        $failed = [];

        foreach ($this->manipulators as $manipulation) {
            try {
                $manipulation->apply($media);
            } catch (\Exception $e) {
                $failed[] = $manipulation;
            } catch (\Throwable $e) {
                $failed[] = $manipulation;
            }
        }

        $media->save();

        if (count($failed)) {
            throw new ManipulationFailedException($failed);
        }
    }
}
