<?php namespace Znck\Attach\Contracts;

interface UriGenerator
{
    public function getUri(Media $media) : string;

    public function getUrlFor(Media $media, string $manipulation) : string;
}
