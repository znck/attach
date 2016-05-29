<?php namespace Znck\Attach\Contracts;

use Znck\Attach\Collection;

interface Manager
{
    public function available() : array;
    
    public function applied() : array;

    public function add(string $name, array $attributes = []) : self;


    /**
     * @param Media|Collection $media
     *
     * @return void
     */
    public function run($media);

    /**
     * @param Media|Collection $media
     *
     * @return void
     */
    public function runOnQueue($media);
}
