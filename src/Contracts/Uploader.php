<?php namespace Znck\Attach\Contracts;

interface Uploader
{
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model|Media
     */
    public function upload($file, array $attributes = []);
}
