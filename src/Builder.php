<?php

namespace Znck\Attach;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\FinderContract;
use Znck\Attach\Contracts\UploaderContract;
use Znck\Attach\Jobs\RunProcessors;
use Znck\Attach\Jobs\RunProcessorsOnQueue;

class Builder
{
    protected static $processors = [];

    protected $uploader;

    protected $normalProcessors = [];

    protected $queuedProcessors = [];

    protected $shouldQueue = false;

    private function __construct(UploaderContract $uploader)
    {
        $this->uploader = $uploader;
    }

    public static function make(Request $request, string $key = 'file', bool $store = true): self
    {
        return self::makeFromFile($request->file($key), $store);
    }

    public static function makeFromFile(UploadedFile $file, bool $store = true): self
    {
        $attachment = app(AttachmentContract::class);
        $finder = app(FinderContract::class);
        $uploader = app(UploaderContract::class, [$file, $attachment, $finder]);

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
        if ($this->isProcessor($name, $parameters)) {
            return $this;
        }

        $parameters = $this->beforeUpload($name, $parameters);

        $result = call_user_func_array([$this->uploader, $name], (array) $parameters);

        $this->afterUpload($name);

        if ($result === $this->uploader) {
            return $this;
        }

        return $result;
    }

    /**
     * @return \Znck\Attach\Contracts\AttachmentContract|Attachment
     */
    protected function getAttachmentFromUploader(): \Znck\Attach\Contracts\AttachmentContract
    {
        return $this->uploader->getAttachment();
    }

    protected function isProcessor(string $name, array $parameters)
    {
        if (isset(self::$processors[$name])) {
            foreach ((array) self::$processors[$name] as $abstract) {
                $processor = app($abstract, (array) $parameters);

                if ($this->shouldQueue) {
                    $this->queuedProcessors[] = $processor;
                } else {
                    $this->normalProcessors[] = $processor;
                }
            }

            $this->shouldQueue = false;

            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @param $parameters
     *
     * @return array
     */
    protected function beforeUpload(string $name, array $parameters): array
    {
        if (hash_equals('upload', $name) and count($parameters)) {
            $arg = array_first($parameters);

            if (is_callable($arg)) {
                call_user_func($arg, $this->getAttachmentFromUploader(), $this->uploader);
            } elseif (is_array($arg)) {
                $this->getAttachmentFromUploader()->fill($arg);
            } elseif (is_string($arg)) {
                $this->getAttachmentFromUploader()->path = $arg;
            }

            return [];
        }

        return $parameters;
    }

    /**
     * @param $name
     */
    protected function afterUpload($name)
    {
        if (! hash_equals('upload', $name)) {
            return;
        }

        if (count($this->normalProcessors)) {
            dispatch(new RunProcessors($this->uploader, $this->normalProcessors));
        }

        if (count($this->queuedProcessors)) {
            dispatch(new RunProcessorsOnQueue($this->uploader, $this->queuedProcessors));
        }
    }
}
