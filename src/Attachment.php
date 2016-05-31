<?php namespace Znck\Attach;

use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\Media;

class Attachment extends Model implements Media
{
    use MediaTrait;

    protected $guarded = [];

    protected $casts = [
        'manipulations' => 'array',
        'properties'    => 'array',
    ];
}
