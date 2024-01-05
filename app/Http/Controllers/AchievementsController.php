<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Service\AchievementService;

class AchievementsController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    public function index(User $user)
    {
        try {
            $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
            $nextAvailableAchievements = $this->achievementService->getNextAvailableAchievements($user);
            $currentBadge = $this->achievementService->getCurrentBadge($user);
            $nextBadge = $this->achievementService->getNextBadge($user);
            $remainingToUnlockNextBadge = $this->achievementService->getRemainingToUnlockNextBadge($user);

            return response()->json([
                'unlocked_achievements' => $unlockedAchievements,
                'next_available_achievements' => $nextAvailableAchievements,
                'current_badge' => $currentBadge,
                'next_badge' => $nextBadge,
                'remaining_to_unlock_next_badge' => $remainingToUnlockNextBadge,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th->getMessage());
        }
    }

}
