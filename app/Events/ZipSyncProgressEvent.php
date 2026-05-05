<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ZipSyncProgressEvent implements ShouldBroadcastNow
{
     use Dispatchable, InteractsWithSockets, SerializesModels;

    public $athleteId;
    public $total;
    public $completed;
    public $progress;
    /**
     * Create a new event instance.
     */
     public function __construct($athleteId, $total, $completed)
    {
        $this->athleteId = $athleteId;
        $this->total = $total;
        $this->completed = $completed;
        $this->progress = $total > 0 
            ? round(($completed / $total) * 100, 2) 
            : 0;
    }
       public function broadcastOn(): array
    {
        return [
            new PrivateChannel('sync-zip.' . $this->athleteId),
        ];
    }


    public function broadcastAs()
    {
        return 'zip.sync.progress';
    }
}
