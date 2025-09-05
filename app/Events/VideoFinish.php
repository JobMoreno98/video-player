<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoFinish implements ShouldBroadcast

{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $video;

    public function __construct($video)
    {
        $this->video = $video;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->video->user_id);
    }

    public function broadcastWith()
    {
        return ['mensaje' => "🎬 Tu video '{$this->video->nombre}' está listo"];
    }
}
