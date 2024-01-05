<?php

namespace App\Service;

use App\Models\User;
use App\Events\BadgeUnlocked;
use App\Events\AchievementUnlocked;
use Illuminate\Support\Facades\Log;

class AchievementService
{

    /**
     *
     * @var Array
     */
    protected $badges;
    /**
     *
     * @var Array
     */
    protected $lessonsWatchedAchievement;
    /**
     *
     * @var Array
     */
    protected $commentsWrittenAchievement;


    public function __construct()
    {
        $this->lessonsWatchedAchievement = config('constant.lessonsWatched');
        $this->commentsWrittenAchievement = config('constant.commentsWritten');
        $this->badges = config('constant.badges');
    }

    public function unlockLessonAchievement(User $user, $lessonsWatched)
    {
        $lessonAchievements = $this->lessonsWatchedAchievement;

        foreach ($lessonAchievements as $achievement) {
            if ($lessonsWatched >= $achievement['value']) {

                event(new AchievementUnlocked($achievement['text'], $user));
            } else {
                break; // Stop checking once the condition is not met
            }
        }
    }

    public function unlockCommentAchievement(User $user, $commentsWritten)
    {
        $commentAchievements = $this->commentsWrittenAchievement;

        foreach ($commentAchievements as $achievement) {
            if ($commentsWritten >= $achievement['value']) {
                event(new AchievementUnlocked($achievement['text'], $user));
            } else {
                break; // Stop checking once the condition is not met
            }
        }
    }

    public function unlockBadge(User $user, $totalAchievements)
    {
        $badges = $this->badges;

        foreach ($badges as $badge) {
            if ($totalAchievements >= $badge['value']) {
                event(new BadgeUnlocked($badge['text'], $user));
            } else {
                break; // Stop checking once the condition is not met
            }
        }
    }


    // unlocked_achievements (string[ ])
    // An array of the user’s unlocked achievements by name
    public function getUnlockedAchievements(User $user)
    {
        $watchCount = $user->watched()->count();
        // $watchCount = $user->watched->count();
        $commentsCount = $user->comments->count();

        $unlockedCommentsAchievement = array_filter($this->commentsWrittenAchievement, function ($comment) use ($commentsCount) {
            return $comment['value'] <= $commentsCount;
        });

        $unlockedWatchedAchievement = array_filter($this->lessonsWatchedAchievement, function ($lesson) use ($watchCount) {
            return $lesson['value'] <= $watchCount;
        });

        return array_merge(array_column($unlockedWatchedAchievement, 'text'), array_column($unlockedCommentsAchievement, 'text'));
    }

    public function getNextAvailableAchievements(User $user)
    {
        $unlockedAchievements = $this->getUnlockedAchievements($user);
        $nextAvailableAchievements = [];
        // Lessons Watched Achievements
        $lessonAchievements = array_diff(
            array_column($this->lessonsWatchedAchievement, 'text'),
            $unlockedAchievements,
        );
        $nextAvailableAchievements[] = reset($lessonAchievements);

        // Comments Written Achievements
        $commentAchievements = array_diff(
            array_column($this->commentsWrittenAchievement, 'text'),
            $unlockedAchievements,
        );
        $nextAvailableAchievements[] = reset($commentAchievements);
        return $nextAvailableAchievements;
    }


    // current_badge (string)
    // The name of the user’s current badge.
    public function getCurrentBadge(User $user)
    {
        $totalAchievements = $this->calculateTotalAchievements($user);

        $currentBadge = '';
        foreach ($this->badges as $badge) {
            if ($totalAchievements < $badge['value']) {
                break;
            }
            $currentBadge = $badge['text'];
        }
        return $currentBadge;
    }

    // next_badge (string)
    // The name of the next badge the user can earn.
    public function getNextBadge(User $user)
    {
        $totalAchievements = $this->calculateTotalAchievements($user);

        $nextBadge = '';
        foreach ($this->badges as $badge) {
            if ($totalAchievements < $badge['value']) {
                $nextBadge = $badge['text'];
                break;
            }
        }

        return $nextBadge;
    }
    // remaining_to_unlock_next_badge (int)
    // An array of the user’s unlocked achievements by name
    public function getRemainingToUnlockNextBadge(User $user)
    {
        $totalAchievements = $this->calculateTotalAchievements($user);

        $remainingToUnlockNextBadge = 0;
        foreach ($this->badges as $badge) {
            if ($totalAchievements < $badge['value']) {
                $remainingToUnlockNextBadge = $badge['value'] - $totalAchievements;
                break;
            }
        }

        return $remainingToUnlockNextBadge;
    }


    protected function calculateTotalAchievements(User $user)
    {
        return $user->watched->count() + $user->comments->count();
    }



}
