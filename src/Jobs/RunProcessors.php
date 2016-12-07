<?php

use Znck\Attach\Jobs;
use Znck\Attach\Contracts\Uploader;
use Znck\Attach\Contracts\Attachment;

class RunProcessors {
    use \Illuminate\Queue\SerializesModels;
    use \Illuminate\Bus\Queueable;

    protected $processors;

    protected $attachment;

    public function __construct(Uploader $uploader, array $processors) {
        $this->processors = $processors;
        $this->attachment = $uploader->getAttachment();
    }

    public function handle(): bool {
        foreach ($processors as $processor) {
            $processor->process($this->attachment);
        }

        if ($this->attachment->isDirty()) {
            $this->attachment->save();
        }

        return true;
    }
}
