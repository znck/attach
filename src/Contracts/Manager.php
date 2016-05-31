<?php namespace Znck\Attach\Contracts;

use Znck\Attach\Collection;

interface Manager
{
    public function available() : array;

    /**
     * @return array
     */
    public function applied() : array;

    /**
     * @param string $name
     * @param array $attributes
     *
     * @return Manager
     */
    public function add(string $name, array $attributes = []) : Manager;


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
