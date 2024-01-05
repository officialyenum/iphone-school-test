<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Service\AchievementService;
use Illuminate\Support\Facades\Log;

class AchievementsController extends Controller
{
    /**
     * @var AchievementService
     */
    protected $achievementService;

    /**
     * AchievementsController constructor.
     *
     * @param AchievementService $achievementService
     */
    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Get achievements information for a user.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(User $user)
    {
        try {
            // Get achievements data using the AchievementService
            $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
            $nextAvailableAchievements = $this->achievementService->getNextAvailableAchievements($user);
            $currentBadge = $this->achievementService->getCurrentBadge($user);
            $nextBadge = $this->achievementService->getNextBadge($user);
            $remainingToUnlockNextBadge = $this->achievementService->getRemainingToUnlockNextBadge($user);

            // Return JSON response with achievements data
            return response()->json([
                'unlocked_achievements' => $unlockedAchievements,
                'next_available_achievements' => $nextAvailableAchievements,
                'current_badge' => $currentBadge,
                'next_badge' => $nextBadge,
                'remaining_to_unlock_next_badge' => $remainingToUnlockNextBadge,
            ]);
        } catch (\Throwable $th) {
            // Log any errors that occur during the achievement retrieval process
            Log::error($th->getMessage());
        }
    }
}
