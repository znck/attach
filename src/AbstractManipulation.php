<?php namespace Znck\Attach;

use Illuminate\Support\Str;
use Znck\Attach\Contracts\Manipulation;

abstract class AbstractManipulation implements Manipulation
{
    /**
     * @var array
     */
    protected $attributes = [];

    protected $imageMimeTypes =  [
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpe' => ['image/jpeg', 'image/pjpeg'],
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
    ];

    public function isImage(string $mime) {
        foreach ($this->imageMimeTypes as $mimes) {
            if (in_array($mime, (array)$mimes)) {
                return true;
            }
        }

        return false;
    }

    public function setAttributes(array $attributes) : Manipulation
    {
        $this->attributes = $attributes;
        
        return $this;
    }

    public function getName() : string
    {
        return Str::lower(static::class);
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }
    
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}
