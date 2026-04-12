<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\GeneratesOsmStaticMap;

class GenerateActivityMap implements ShouldQueue
{
    use GeneratesOsmStaticMap;

    public function __construct(public $activity) {}

    public function handle()
    {
        $this->generateOsmMap($this->activity);
    }
}
