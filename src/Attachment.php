<?php namespace Znck\Attach;

use Illuminate\Database\Eloquent\Model;
use Znck\Attach\Contracts\Media;

class Attachment extends Model implements Media
{
    use MediaTrait;
}
