<?php

namespace App\Events;

use App\Models\DemoRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DemoRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public DemoRequest $demoRequest) {}
}
