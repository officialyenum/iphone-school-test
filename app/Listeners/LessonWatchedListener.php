<?php

namespace App\Listeners;

use App\Events\LessonWatched;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LessonWatchedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LessonWatched $event): void
    {
        // Logic to check lessons watched achievements
        // ...

        $achievementName = "";
        $badgeName = "";

        // **Lessons Watched Achievements**

        // - First Lesson Watched
        // - 5 Lessons Watched
        // - 10 Lessons Watched
        // - 25 Lessons Watched
        // - 50 Lessons Watched

        // **Comments Written Achievements**

        // - First Comment Written
        // - 3 Comments Written
        // - 5 Comments Written
        // - 10 Comments Written
        // - 20 Comments Written

        // **Badges**

        // Users also have a badge, this is determined by the number of achievements they have unlocked.

        // - Beginner: 0 Achievements
        // - Intermediate: 4 Achievements
        // - Advanced: 8 Achievements
        // - Master: 10 Achievements
        $lessonsWatchedCount = 1;
        $commentsWrittenCount = 1;
        switch ($lessonsWatchedCount) {
            case 1:
                # code...
                break;
            case 5:
                # code...
                break;
            case 10:
                # code...
                break;
            case 25:
                # code...
                break;
            case 50:
                # code...
                break;
        }
        switch ($commentsWrittenCount) {
            case 1:
                # code...
                break;
            case 5:
                # code...
                break;
            case 10:
                # code...
                break;
            case 25:
                # code...
                break;
            case 50:
                # code...
                break;
        }
        // Fire AchievementUnlocked event if an achievement is unlocked
        event(new AchievementUnlocked($achievementName, $event->user));

        // Fire BadgeUnlocked event if a new badge is earned
        event(new BadgeUnlocked($badgeName, $event->user));
    }
}
