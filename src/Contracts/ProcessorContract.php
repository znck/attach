<?php namespace Znck\Attach\Contracts;

interface ProcessorContract
{
    public function process(AttachmentContract $attachment);
}
