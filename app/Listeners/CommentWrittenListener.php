<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Events\CommentWritten;
use App\Events\AchievementUnlocked;
use App\Service\AchievementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentWrittenListener
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
    public function handle(CommentWritten $event): void
    {
        // Logic to check comments written achievements
        // ...
        $comment = $event->comment;
        $user = $comment->user;
        $achievementName = null;
        $badgeName = null;

        // **Comments Written Achievements**

        // - First Comment Written
        // - 3 Comments Written
        // - 5 Comments Written
        // - 10 Comments Written
        // - 20 Comments Written
        $commentsWrittenCount = $user->comments()->count();
        switch ($commentsWrittenCount) {
            case 1:
                $achievementName = config('constant.commentsWritten')[0]['text'];
                break;
            case 3:
                # code...
                $achievementName = config('constant.commentsWritten')[1]['text'];
                break;
            case 5:
                # code...
                $achievementName = config('constant.commentsWritten')[2]['text'];
                break;
            case 10:
                # code...
                $achievementName = config('constant.commentsWritten')[3]['text'];
                break;
            case 20:
                # code...
                $achievementName = config('constant.commentsWritten')[4]['text'];
                break;
        }
        if($achievementName){
            // Fire AchievementUnlocked event if an achievement is unlocked
            event(new AchievementUnlocked($achievementName, $user));
        }

        // **Badges**

        // Users also have a badge, this is determined by the number of achievements they have unlocked.

        // - Beginner: 0 Achievements
        // - Intermediate: 4 Achievements
        // - Advanced: 8 Achievements
        // - Master: 10 Achievements
        $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
        Log::info("achievementCount");
        Log::info($unlockedAchievements);
        switch (count($unlockedAchievements)) {
            case 0:
                $badgeName = config('constant.badges')[0]['text'];
                break;
            case 4:
                # code...
                $badgeName = config('constant.badges')[1]['text'];
                break;
            case 8:
                # code...
                $badgeName = config('constant.badges')[2]['text'];
                break;
            case 10:
                # code...
                $badgeName = config('constant.badges')[3]['text'];
                break;
        }
        Log::info("Badge Earned");
        Log::info($badgeName);
        if($badgeName){
            // Fire BadgeUnlocked event if a new badge is earned
            event(new BadgeUnlocked($badgeName, $user));
        }
    }
}
