<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Events\LessonWatched;
use App\Events\AchievementUnlocked;
use App\Service\AchievementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LessonWatchedListener
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
    public function handle(LessonWatched $event): void
    {
        // Logic to check lessons watched achievements
        // ...
        $lesson = $event->lesson;
        $user = $event->user;
        $user->lessons()->attach($lesson, ['watched' => true]);
        $achievement_name = "";
        $badge_name = "";

        // **Lessons Watched Achievements**

        // - First Lesson Watched
        // - 5 Lessons Watched
        // - 10 Lessons Watched
        // - 25 Lessons Watched
        // - 50 Lessons Watched

        // $lessonsWatchedCount = $user->lessons->where('watched',1)->count();
        $lessonsWatchedCount = $user->watched()->count();

        // $commentsWrittenCount = $user->comments()->count();
        switch ($lessonsWatchedCount) {
            case 1:
                $achievement_name = config('constant.lessonsWatched')[0]['text'];
                break;
            case 5:
                $achievement_name = config('constant.lessonsWatched')[1]['text'];
                break;
            case 10:
                $achievement_name = config('constant.lessonsWatched')[2]['text'];
                break;
            case 25:
                $achievement_name = config('constant.lessonsWatched')[3]['text'];
                break;
            case 50:
                $achievement_name = config('constant.lessonsWatched')[4]['text'];
                break;
        }

        if($achievement_name){
            // Fire AchievementUnlocked event if an achievement is unlocked
            event(new AchievementUnlocked($achievement_name, $user));
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
                $badge_name = config('constant.badges')[0]['text'];
                break;
            case 4:
                $badge_name = config('constant.badges')[1]['text'];
                break;
            case 8:
                $badge_name = config('constant.badges')[2]['text'];
                break;
            case 10:
                $badge_name = config('constant.badges')[3]['text'];
                break;
        }
        Log::info("Badge Earned");
        Log::info($badge_name);
        if($badge_name){
            // Fire BadgeUnlocked event if a new badge is earned
            event(new BadgeUnlocked($badge_name, $event->user));
        }
    }
}
