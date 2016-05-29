<?php namespace Znck\Attach\Uploaders;

use Illuminate\Container\Container;
use Znck\Attach\Contracts\Manager;
use Znck\Attach\Contracts\Media;
use Znck\Attach\Contracts\Uploader;

class DefaultUploader implements Uploader
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Container
     */
    protected $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->manager = $this->container->make(Manager::class);
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array $attributes
     * 
     * @return \Illuminate\Database\Eloquent\Model|void|Media
     */
    public function upload($file, array $attributes = [])
    {
        $media = $this->makeModel($attributes + ['mime' => $file->getMimeType(), 'filename' => $file->getClientOriginalName()]);
        
        $media->save();


        if (count($this->manager->applied())) {
            $manipulations = $this->manager->available();

            foreach ($manipulations as $manipulation) {
                $this->manager->add($manipulation);
            }
        }
        
        $this->manager->runOnQueue($media);
        
        return $media;
    }
    
    protected function makeModel(array $attributes)
    {
        return $this->container->make(config('attach.model'), [$attributes]);
    }
}
