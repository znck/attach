<?php namespace Znck\Attach;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Znck\Attach\Contracts\AttachmentContract;
use Znck\Attach\Contracts\UploaderContract;

class Uploader implements UploaderContract
{
    /**
     * Uploaded by.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * Uploaded for.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $related;

    /**
     * Uploaded file.
     *
     * @var \Illuminate\Http\UploadedFile
     */
    protected $file;

    /**
     * @var Model|Contracts\AttachmentContract|Attachment
     */
    protected $attachment;

    /**
     * @var Contracts\FinderContract
     */
    protected $finder;

    /**
     * Uploader constructor.
     *
     * @param \Illuminate\Http\UploadedFile             $file
     * @param \Znck\Attach\Contracts\AttachmentContract $attachment
     */
    public function __construct(UploadedFile $file, AttachmentContract $attachment)
    {
        $this->attachment = $attachment;
        $this->file = $file;
    }

    /**
     * Add attachment owner.
     *
     * @param \Illuminate\Database\Eloquent\Model $owner
     *
     * @return $this
     */
    public function owner(Model $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Upload the file.
     *
     * @return \Znck\Attach\Contracts\AttachmentContract
     */
    public function upload(): AttachmentContract
    {
        $this->attachment->filename = $this->file->getClientOriginalName();
        $this->attachment->mime = $this->file->getMimeType();
        $this->attachment->size = $this->file->getSize();
        $this->attachment->extension = $this->file->getClientOriginalExtension();
        $this->attachment->visibility = $this->attachment->visibility ?? 'private';

        $this->moveUploadedFile();

        if ($this->owner) {
            $this->attachment->owner()->associate($this->owner);
        }

        if (! $this->attachment->save()) {
            $this->unlinkUploadedFile();

            throw new UploadException('Cannot store uploaded file meta in database.');
        }

        return $this->attachment;
    }

    /**
     * File uploaded by user.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * File uploaded for.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get attachment.
     *
     * @return Contracts\AttachmentContract|Model|Attachment
     */
    public function getAttachment(): AttachmentContract
    {
        return $this->attachment;
    }

    /**
     * Prepare content dependent filename.
     *
     * @return string
     */
    protected function getFilename(): string
    {
        return md5_file($this->file->getRealPath()).'.'.$this->getAttachment()->extension;
    }

    /**
     * Set related model.
     *
     * @param \Illuminate\Database\Eloquent\Model $related
     *
     * @return $this
     */
    public function attachTo(Model $related)
    {
        $this->related = $related;

        return $this;
    }

    /**
     * Move uploaded file to storage.
     *
     * @return void
     */
    protected function moveUploadedFile()
    {
        $name = $this->getFilename();

        $this->attachment->path = $path = rtrim($this->attachment->path, DIRECTORY_SEPARATOR);
        $this->attachment->path .= DIRECTORY_SEPARATOR.$name;

        $this->finder->putAs($path, $this->file, $name, $this->attachment->visibility);
    }

    /**
     * Deleted uploaded file.
     *
     * @return void
     */
    protected function unlinkUploadedFile()
    {
        $this->finder->unlink($this->attachment);
    }
}
