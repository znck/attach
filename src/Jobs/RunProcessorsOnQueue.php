<?php
namespace Znck\Attach\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class RunProcessorsOnQueue extends RunProcessors implements ShouldQueue
{
}
