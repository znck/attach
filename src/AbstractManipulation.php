<?php namespace Znck\Attach;

use Illuminate\Support\Str;
use Znck\Attach\Contracts\Manipulation;

abstract class AbstractManipulation implements Manipulation
{
    /**
     * @var array
     */
    protected $attributes = [];

    public function setAttributes(array $attributes) : self
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
