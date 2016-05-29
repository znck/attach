<?php namespace Znck\Attach;

use Illuminate\Contracts\Routing\UrlGenerator;
use Znck\Attach\Contracts\Media;

class UriGenerator implements Contracts\UriGenerator
{
    protected $fields;
    /**
     * @var UrlGenerator
     */
    protected $url;
    /**
     * @var string
     */
    protected $name;

    public function __construct(UrlGenerator $urlGenerator, $name, array $fields)
    {
        $this->url = $urlGenerator;
        $this->name = $name;
        $this->fields = $fields;
    }

    public function getUri(Media $media)
    {
        return $this->getUrlWith($this->getMediaParameters($media));
    }

    public function getUrlFor(Media $media, string $manipulation) :string
    {
        return $this->getUrlWith($this->getMediaParameters($media) + compact('manipulation'));
    }

    protected function getUrlWith(array $parameters)
    {
        return $this->url->route($this->name, $parameters);
    }

    /**
     * @param Media $media
     *
     * @return mixed
     */
    private function getMediaParameters(Media $media)
    {
        $parameters = [];
        $attributes = $media->toArray();

        foreach ($this->fields as $field => $key) {
            $parameters[$key] = array_get($attributes, $field);
        }

        return $parameters + [$media->getSecureTokenKey() => $media->getSecureToken()];
    }
}
