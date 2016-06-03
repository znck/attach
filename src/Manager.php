<?php namespace Znck\Attach;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Znck\Attach\Contracts\Manipulation;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Exceptions\ManipulationFailedException;
use Znck\Attach\Exceptions\ManipulationNotFoundException;

class Manager implements Contracts\Manager
{
    use Queueable, SerializesModels, InteractsWithQueue, DispatchesJobs;

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
    }

    public function available() : array
    {
        $glob = glob(__DIR__.'/Manipulators/*.php') ?: [];

        return array_map(function ($file) {
            return explode('.', basename($file), 2)[0];
        }, $glob);
    }

    public function applied() : array
    {
        return array_map(function ($item) {
            return class_basename($item);
        }, $this->manipulators);
    }

    public function add(string $name, array $attributes = []) : Contracts\Manager
    {
        $this->manipulators[] = $this->getManipulator($name)->setAttributes($attributes);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Manipulation
     */
    protected function getManipulator(string $name)
    {
        $manipulator = null;

        if (in_array($name, $this->available())) {
            $manipulator = $this->container->make('\\Znck\\Attach\\Manipulators\\'.Str::studly($name));
        } elseif (class_exists($name) or $this->container->resolved($name)) {
            $manipulator = $this->container->make($name);
        }

        if (! $manipulator instanceof Manipulation) {
            throw new ManipulationNotFoundException();
        }

        return $manipulator;
    }

    public function run($media)
    {
        $this->media = $media;
        $this->handle();
    }

    public function runOnQueue($media)
    {
        $this->media = $media;
        $this->dispatch($this);
    }

    public function handle()
    {
        if ($this->media instanceof Collection) {
            $this->media->each(function (Media $media) {
                $this->runManipulations($media);
            });
        } elseif ($this->media instanceof Media) {
            $this->runManipulations($this->media);
        } else {
            throw new InvalidArgumentException();
        }
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
