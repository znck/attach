<?php namespace Znck\Attach\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface Downloader
{
    public function findAttachment(string $filename) : Attachment;

    public function response(string $filename = null) : Response;

    public function download(string $filename = null) : Response;
}
