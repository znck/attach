<?php
namespace Znck\Attach\Jobs;

use Znck\Attach\Contracts\UploaderContract;

class RunProcessors
{
    use \Illuminate\Queue\SerializesModels;
    use \Illuminate\Bus\Queueable;

    protected $processors;

    /**
     * @var \Znck\Attach\Contracts\AttachmentContract|\Znck\Attach\Attachment
     */
    protected $attachment;

    public function __construct(UploaderContract $uploader, array $processors)
    {
        $this->processors = $processors;
        $this->attachment = $uploader->getAttachment();
    }

    public function handle(): bool
    {
        foreach ($this->processors as $processor) {
            $processor->process($this->attachment);
        }

        if ($this->attachment->isDirty()) {
            $this->attachment->save();
        }

        return true;
    }
}
