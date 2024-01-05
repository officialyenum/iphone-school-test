<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BadgeUnlocked
{

    use Dispatchable, SerializesModels;

    public $badgeName;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($badgeName, User $user)
    {
        $this->badgeName = $badgeName;
        $this->user = $user;
    }
}
