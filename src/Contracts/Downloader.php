<?php

namespace Znck\Attach\Contracts;

interface Downloader
{
    /**
     * Respond with file.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract $attachment
     * @param string $variation
     *
     * @return \Illuminate\Http\Response
     */
    public function response(AttachmentContract $attachment, string $variation = null);

    /**
     * Download a file.
     *
     * @param \Znck\Attach\Contracts\AttachmentContract $attachment
     * @param string|null $variation
     *
     * @return \Illuminate\Http\Response
     */
    public function download(AttachmentContract $attachment, string $variation = null);
}
