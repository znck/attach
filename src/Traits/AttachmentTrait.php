<?php namespace Znck\Attach\Traits;

trait AttachmentTrait
{
    public function getAttachmentKey(): string
    {
        return $this->{$this->getAttachmentKeyName()};
    }

    public function getAttachmentKeyName(): string
    {
        return 'id';
    }

    /**
     * Get path.
     *
     * @param string $variation
     *
     * @return string
     */
    public function getPath(string $variation = null): string
    {
        if (! $variation) {
            return $this->path;
        }

        return str_replace_last($this->extension, "${variation}.{$this->extension}", $this->path);
    }
}
