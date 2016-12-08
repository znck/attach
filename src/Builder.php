<?php

namespace Znck\Attach;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\ShouldQueue;
use Znck\Attach\Contracts\Uploader as UploaderInterface;
use Znck\Attach\Jobs\RunProcessors;
use Znck\Attach\Jobs\RunProcessorsOnQueue;

class Builder
{
    protected static $processors = [];

    protected $uploader;

    protected $normalProcessors = [];

    protected $queuedProcessors = [];

    protected $shouldQueue = false;

    private function __construct(UploaderInterface $uploader)
    {
        $this->uploader = $uploader;
    }

    public static function make(Request $request, string $key = 'file', bool $store = true): self
    {
        return self::makeFromFile($request->file($key), $store);
    }

    public static function makeFromFile(UploadedFile $file, bool $store = true): self
    {
        $attachment = app(Attachment::class);
        $uploader = app(UploaderInterface::class, [$file, $attachment, $store]);

        return new self($uploader);
    }

    public static function register(string $name, $abstract)
    {
        if (array_key_exists($name, self::$processors)) {
            self::$processors[$name] = array_merge((array) self::$processors[$name], (array) $abstract);
        } else {
            self::$processors[$name] = $abstract;
        }
    }

    public function queue()
    {
        $this->shouldQueue = true;

        return $this;
    }

    public function __call($name, $parameters)
    {
        if (array_key_exists($name, self::$processors)) {
            foreach ((array) self::$processors[$name] as $abstract) {
                $processor = app($abstract, (array) $parameters);

                if ($this->shouldQueue or $processor instanceof ShouldQueue) {
                    $this->queuedProcessors[] = $processor;
                } else {
                    $this->normalProcessors[] = $processor;
                }
            }

            $this->shouldQueue = false;

            return $this;
        }

        if (hash_equals('upload', $name) and count($parameters)) {
            $arg = array_first($parameters);

            if (is_callable($arg)) {
                call_user_func($arg, $this->uploader->getAttachment(), $this->uploader);
            } elseif (is_array($arg)) {
                $this->uploader->getAttachment()->fill($arg);
            } elseif (is_string($arg)) {
                $this->uploader->getAttachment()->path = $arg;
            }

            $parameters = [];
        }

        $result = call_user_func_array([$this->uploader, $name], (array) $parameters);

        if (hash_equals('upload', $name)) {
            if (count($this->normalProcessors)) {
                dispatch(new RunProcessors($this->uploader, $this->normalProcessors));
            }
            if (count($this->queuedProcessors)) {
                dispatch(new RunProcessorsOnQueue($this->uploader, $this->queuedProcessors));
            }
        }

        if ($result === $this->uploader) {
            return $this;
        }

        return $result;
    }
}
