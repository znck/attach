<?php

namespace Znck\Attach\Util;

use Znck\Attach\Contracts\Attachment as AttachmentInterface;

class Attachment implements Attachment {
    use \Znck\Attach\Traits\Attachment;

    public function owner() {
        return $this->belongTo(config('attach.user', config('auth.providers.users.model')));
    }

    public function related() {
        return $this->morphTo();
    }
}
