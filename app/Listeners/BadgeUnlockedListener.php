<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Service\AchievementService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BadgeUnlockedListener
{
    /**
     * @var AchievementService
     */
    protected $achievementService;
    /**
     * Create the event listener.
     *
     * @param AchievementService $achievementService
     */
    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Handle the event.
     */
    public function handle(BadgeUnlocked $event): void
    {
        Log::info($event->badgeName. ' unlocked by ' . $event->user->name);
    }
}
