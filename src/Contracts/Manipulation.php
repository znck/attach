<?php namespace Znck\Attach\Contracts;

interface Manipulation
{
    public function setAttributes(array $attributes) : self;

    public function getName() : string;
    
    public function apply(Media $media);
}
