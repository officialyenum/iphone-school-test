<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Service\AchievementService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AchievementUnlockedListener
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
    public function handle(AchievementUnlocked $event): void
    {
        Log::info($event->achievementName. ' unlocked by ' . $event->user->name);
    }
}
