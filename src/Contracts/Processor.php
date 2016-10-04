<?php namespace Znck\Attach\Contracts;

interface Processor
{
    public function process(Attachment $attachment);
}
