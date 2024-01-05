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

class AchievementUnlocked
{
    use Dispatchable, SerializesModels;

    public $achievementName;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($achievementName, User $user)
    {
        $this->achievementName = $achievementName;
        $this->user = $user;
    }

}
