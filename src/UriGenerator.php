<?php namespace Znck\Attach;

use Illuminate\Contracts\Routing\UrlGenerator as IlluminateUrlGenerator;
use Znck\Attach\Contracts\Media;

class UriGenerator implements Contracts\UriGenerator
{
    protected $fields;
    /**
     * @var IlluminateUrlGenerator
     */
    protected $url;
    /**
     * @var string
     */
    protected $name;

    public function __construct(IlluminateUrlGenerator $urlGenerator, $name, array $fields)
    {
        $this->url = $urlGenerator;
        $this->name = $name;
        $this->fields = $fields;
    }

    public function getUri(Media $media) : string
    {
        return $this->getUrlWith($this->getMediaParameters($media));
    }

    public function getUrlFor(Media $media, string $manipulation) :string
    {
        return $this->getUrlWith($this->getMediaParameters($media, $manipulation));
    }

    protected function getUrlWith(array $parameters)
    {
        return $this->url->route($this->name, $parameters);
    }

    /**
     * @param Media  $media
     * @param string $manipulation
     *
     * @return mixed
     */
    private function getMediaParameters(Media $media, string $manipulation = null)
    {
        $parameters = [];
        $attributes = $media->toArray();

        foreach ($this->fields as $field => $key) {
            if (hash_equals($field, 'manipulation')) {
                $parameters[$key] = $manipulation;
                $manipulation = false;
            } else {
                $parameters[$key] = array_get($attributes, $field);
            }
        }

        if ($manipulation !== false) {
            $parameters['manipulation'] = $manipulation;
        }

        return $parameters + [$media->getSecureTokenKey() => $media->getSecureToken()];
    }
}
