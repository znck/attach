<?php

namespace Znck\Attach\Util;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model implements \Znck\Attach\Contracts\Attachment
{
    use \Znck\Attach\Traits\Attachment;

    public function owner()
    {
        return $this->belongsTo(config('attach.user', config('auth.providers.users.model')));
    }

    public function related()
    {
        return $this->morphTo();
    }
}
