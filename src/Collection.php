<?php namespace Znck\Attach;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Znck\Attach\Contracts\Media;

class Collection extends EloquentCollection
{
    protected $collection_name;

    private $model;

    public function __construct($collection_name, Media $model, $items = [])
    {
        parent::__construct($items);
        $this->model = $model;
        if ($collection_name) {
            $this->setCollectionAccessor($collection_name);
        }
    }

    public function setCollectionAccessor(string $name)
    {
        $this->collection_name = $name;
        $this->items = $this->getArrayableItems($this->model->where('collection', $name)->get());
    }

    public function getCollectionAccessor() : string
    {
        return $this->collection_name;
    }
}
