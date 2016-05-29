<?php namespace Znck\Attach;

use Znck\Attach\Contracts\TokenGenerator;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Contracts\UriGenerator;
use Znck\Attach\Exceptions\FilesystemException;
use Znck\Attach\Exceptions\ManipulationNotFoundException;

trait MediaTrait #extends \Illuminate\Database\Eloquent\Model implements Contracts\Media
{
    private $imageMimeTypes = [];

    private $pdfMimeTypes = [];

    private $type;

    public function getCasts()
    {
        $casts = parent::getCasts();

        return $casts + [
            'manipulations' => 'array',
            'properties'    => 'array',
        ];
    }

    public function getFillable()
    {
        $fillable = parent::getFillable();

        if (! count($fillable)) {
            return [
                'properties',
                'collection',
                'title',
                'filename',
                'disk',
                'path',
                'order',
            ];
        }

        return $fillable;
    }
    
    private function getImageMimeTypes()
    {
        return array_flip($this->imageMimeTypes);
    }
    
    private function getPdfMimeTypes()
    {
        return array_flip($this->pdfMimeTypes);
    }

    public function getType() : int
    {
        if (! $this->type) {
            if (array_key_exists($this->mime, $this->getImageMimeTypes())) {
                $this->type = Media::TYPE_IMAGE;
            } elseif (array_key_exists($this->mime, $this->getPdfMimeTypes())) {
                $this->type = Media::TYPE_PDF;
            } else {
                $this->type = Media::TYPE_OTHER;
            }
        }
        
        return $this->type;
    }

    public function getUri(string $manipulation = null): string
    {
        return $manipulation
            ? $this->getUriGenerator()->getUrlFor($this, $manipulation)
            : $this->getUriGenerator()->getUri($this);
    }

    public function getContent()
    {
        return $this->getFilesystem()->get($this->getPath());
    }

    public function getHttpHeaders(): array
    {
        return $this->prepareHeadersWith();
    }

    public function getVisibility(): string
    {
        return $this->attributes['visibility'];
    }

    public function setVisibility(string $visibility): self
    {
        $this->setVisibilityAttribute($visibility);
    }

    public function getCollection(): Collection
    {
        return new Collection($this->collection, new static());
    }

    public function availableManipulations(): array
    {
        return array_keys($this->manipulations);
    }
    
    public function getSecureTokenKey() : string
    {
        return 'token';
    }

    public function getSecureToken(): string
    {
        return $this->getEncrypter()->make($this->getKey());
    }

    public function verifySecureToken(string $hash): bool
    {
        return $this->getEncrypter()->verify($hash, $this->getKey());
    }

    /**
     * @param mixed $file
     *
     * @throws FilesystemException
     */
    public function setContent($file)
    {
        $this->putContent($file, $this->getPath());
    }

    /**
     * @param string $name
     * @param mixed $file
     * @param string $mime
     *
     * @throws FilesystemException
     *
     * @return bool
     */
    public function setManipulation(string $name, $file, $mime) : bool
    {
        $path = $this->getPath($name);
        $this->putContent($path, $file);
        $size = $this->getFilesystem()->size($path);

        $this->manipulations[$name] = compact('mime', 'size');

        $this->save();
    }

    public function getManipulationHeader(string $name) : array
    {
        if (! array_key_exists($name, $this->manipulations)) {
            throw new ManipulationNotFoundException();
        }

        $mime = $this->manipulations[$name]['mime'];
        $size = $this->manipulations[$name]['size'];

        return $this->prepareHeadersWith($mime, $size);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getManipulationContent(string $name)
    {
        if (! array_key_exists($name, $this->manipulations)) {
            throw new ManipulationNotFoundException();
        }
        
        return $this->getFilesystem()->get($this->getPath($name));
    }

    private function prepareHeadersWith($mime = null, $size = null)
    {
        return [
            'Content-Length' => $size ?? $this->size,
            'Content-Type'   => $mime ?? $this->mime,
        ]
        + ($this->getVisibility() === Media::VISIBILITY_PUBLIC ? []: ['private']);
    }

    /**
     * @param string $visibility
     */
    protected function setVisibilityAttribute(string $visibility)
    {
        $visibilities = [Media::VISIBILITY_PRIVATE, Media::VISIBILITY_PUBLIC, Media::VISIBILITY_SHARED];

        if (array_key_exists($visibility, array_flip($visibilities))) {
            $this->attributes['visibility'] = $visibility;
        }
    }

    public function setPathAttribute($value)
    {
        $old = array_get($this->attributes, 'path');

        if ($old and $this->getFilesystem()->exists($old)) {
            $this->moveAll($old, $value);
        }

        $this->attributes['value'] = $value;
    }
    
    public function getPathAttribute()
    {
        return array_get($this->attributes, 'path', config('attach.upload.path'));
    }
    
    public function getDiskAttribute()
    {
        return array_get($this->attributes, 'disk', config('attach.upload.disk'));
    }

    /**
     * @return TokenGenerator
     */
    private function getEncrypter()
    {
        return app(TokenGenerator::class);
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function getFilesystem()
    {
        return app('filesystem')->disk($this->disk);
    }

    /**
     * @return UriGenerator
     */
    private function getUriGenerator()
    {
        return app(UriGenerator::class);
    }

    private function getPath($manipulation = null, $path = null)
    {
        $path = $path ?? $this->path;
        if ($manipulation) {
            $directory = pathinfo($path, PATHINFO_DIRNAME);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = pathinfo($path, PATHINFO_FILENAME);

            return $directory.DIRECTORY_SEPARATOR.$filename.'-'.$manipulation.$extension;
        }
        return $path;
    }

    /**
     * @param string $path
     * @param mixed $file
     *
     * @throws FilesystemException
     */
    private function putContent($path, $file)
    {
        if (! $this->getFilesystem()->put($path, $file)) {
            throw new FilesystemException();
        }
    }

    private function moveAll($old, $new)
    {
        $items = array_keys($this->manipulations);
        $filesystem = $this->getFilesystem();
        $filesystem->move($this->getPath(null, $old), $this->getPath(null, $new));
        
        foreach ($items as $item) {
            $filesystem->move($this->getPath($item, $old), $this->getPath($item, $new));
        }
    }
}
