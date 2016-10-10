<?php namespace Znck\Attach;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Finder;
use Znck\Attach\Contracts\Storage;
use Znck\Attach\Contracts\Uploader as UploaderInterface;
use Znck\Attach\Processors\Store;

class Uploader implements UploaderInterface
{
    protected $related;

    protected $owner;

    protected $file;

    protected $attachment;

    protected $finder;

    public function __construct(UploadedFile $file, Attachment $attachment, $store = true)
    {
        $this->setFile($file);
        $this->setAttachment($attachment);

        if ($store) {
            (new Store($file))->process($attachment);
        }

        return $this;
    }

    public function attachTo(Model $model) : UploaderInterface
    {
        $this->setRelated($model);

        return $this;
    }

    public function owner(Model $model) : UploaderInterface
    {
        $this->setOwner($model);

        return $this;
    }

    public function upload()
    {
        /** @var Attachment|Model $attachment */
        $attachment = $this->getAttachment();
        if ($this->getOwner()) {
            $attachment->owner()->associate($this->getOwner());
        }

        if ($this->getRelated()) {
            $attachment->saved(
                function (Attachment $attachment) {
                    $attachment->related()->save($this->getRelated());
                }
            );
        }

        $file = $this->getFile();
        $attachment->filename = $file->getClientOriginalName();
        $attachment->mime = $file->getMimeType();
        $attachment->size = $file->getSize();
        $attachment->extension = $file->getExtension();
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(Model $owner)
    {
        $this->owner = $owner;
    }

    public function getRelated()
    {
        return $this->related;
    }

    public function setRelated(Model $related)
    {
        $this->related = $related;
    }

    public function getFile() : UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file)
    {
        if (! $file->isValid()) {
            throw new UploadException();
        }

        $this->file = $file;
    }

    public function getAttachment() : Attachment
    {
        return $this->attachment;
    }

    public function setAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function getFinder() : Finder
    {
        if (! $this->finder) {
            $this->finder = app(Finder::class);
        }

        return $this->finder;
    }

    public function setStorage(Storage $storage) : UploaderInterface
    {
        $this->getFinder()->setStorage($storage);

        return $this;
    }
}
