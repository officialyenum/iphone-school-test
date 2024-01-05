<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    /**
     * @var array
     */
    protected $badges;

    /**
     * @var array
     */
    protected $lessonsWatchedAchievement;

    /**
     * @var array
     */
    protected $commentsWrittenAchievement;

    /**
     * AchievementService constructor.
     */
    public function __construct()
    {
        // Load achievement configurations from constants
        $this->lessonsWatchedAchievement = config('constant.lessonsWatched');
        $this->commentsWrittenAchievement = config('constant.commentsWritten');
        $this->badges = config('constant.badges');
    }

    /**
     * Get unlocked achievements by name.
     *
     * @param User $user
     * @return array
     */
    public function getUnlockedAchievements(User $user)
    {
        $watchCount = $user->watched()->count();
        $commentsCount = $user->comments()->count();

        // Filter unlocked comments achievements
        $unlockedCommentsAchievement = array_filter($this->commentsWrittenAchievement, function ($comment) use ($commentsCount) {
            return $comment['value'] <= $commentsCount;
        });

        // Filter unlocked watched achievements
        $unlockedWatchedAchievement = array_filter($this->lessonsWatchedAchievement, function ($lesson) use ($watchCount) {
            return $lesson['value'] <= $watchCount;
        });

        // Merge and return unlocked achievements
        return array_merge(
            array_column($unlockedWatchedAchievement, 'text'),
            array_column($unlockedCommentsAchievement, 'text')
        );
    }

    /**
     * Get next available achievements by name.
     *
     * @param User $user
     * @return array
     */
    public function getNextAvailableAchievements(User $user)
    {
        $unlockedAchievements = $this->getUnlockedAchievements($user);
        $nextAvailableAchievements = [];

        // Lessons Watched Achievements
        $lessonAchievements = array_diff(
            array_column($this->lessonsWatchedAchievement, 'text'),
            $unlockedAchievements
        );
        $nextAvailableAchievements[] = reset($lessonAchievements);

        // Comments Written Achievements
        $commentAchievements = array_diff(
            array_column($this->commentsWrittenAchievement, 'text'),
            $unlockedAchievements
        );
        $nextAvailableAchievements[] = reset($commentAchievements);

        return $nextAvailableAchievements;
    }

    /**
     * Get the name of the user's current badge.
     *
     * @param User $user
     * @return string
     */
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

    /**
     * Get the name of the next badge the user can earn.
     *
     * @param User $user
     * @return string
     */
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

    /**
     * Get the remaining number of achievements to unlock the next badge.
     *
     * @param User $user
     * @return int
     */
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

    /**
     * Calculate the total number of achievements for a user.
     *
     * @param User $user
     * @return int
     */
    protected function calculateTotalAchievements(User $user)
    {
        return $user->watched()->count() + $user->comments()->count();
    }
}
