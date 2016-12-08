<?php namespace Znck\Attach\Traits;

trait Attachment
{
    public function getAttachmentKey() : string
    {
        return $this->{$this->getAttachmentKeyName()};
    }

    public function getAttachmentKeyName() : string
    {
        return 'id';
    }
}
