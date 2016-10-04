<?php namespace Znck\Attach\Processors;

use Znck\Attach\Contracts\Attachment;
use Znck\Attach\Contracts\Processor;

class SaveIfDirty implements Processor
{
    public function process(Attachment $attachment) {
        /** @var \Illuminate\Database\Eloquent\Model $attachment */
        $attachment->saved(
            function (Attachment $attachment) {
                /** @var \Illuminate\Database\Eloquent\Model $attachment */
                if ($attachment->isDirty()) {
                    $attachment->save();
                }
            },
            PHP_INT_MIN
        );
    }
}
