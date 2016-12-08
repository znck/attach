<?php

namespace Znck\Attach;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\ShouldQueue;
use Znck\Attach\Contracts\Uploader as UploaderInterface;

class Builder
{
    protected static $processors = [];

    protected $uploader;

    protected $normalProcessors = [];

    protected $queuedProcessors = [];

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

    public function __call($name, $parameters)
    {
        if (array_key_exists($name, self::$processors)) {
            foreach ((array) self::$processors[$name] as $abstract) {
                $processor = app($abstract, (array) $parameters);

                if ($processor instanceof ShouldQueue) {
                    $this->queuedProcessors[] = $processor;
                } else {
                    $this->normalProcessors[] = $processor;
                }
            }

            return $this;
        }

        if (hash_equals('upload', $name) and count($parameters)) {
            $callback = array_first($parameters);

            if (is_callable($callback)) {
                call_user_func($callback, $this->uploader->getAttachment(), $this->uploader);
            } elseif (is_array($callback)) {
                $this->uploader->getAttachment()->fill($parameters);
            } else {
                $this->uploader->getAttachment()->fill($parameters);
            }
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
