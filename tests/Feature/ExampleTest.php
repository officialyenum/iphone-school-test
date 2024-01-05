<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Comment;
use App\Events\BadgeUnlocked;
use App\Events\LessonWatched;
use App\Events\CommentWritten;
use Illuminate\Support\Facades\DB;
use App\Events\AchievementUnlocked;
use App\Service\AchievementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use App\Listeners\LessonWatchedListener;
use App\Listeners\CommentWrittenListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $lessons;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        // Create a user for the test
        $this->user = User::factory()->create();
        // Create 25 lessons
        $this->lessons = Lesson::factory()->count(25)->create();
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_the_application_event_dispatches()
    {
        Event::fake();
        // Arrange: Create a user and initialize necessary variables
        $user = User::factory()->create();

        // Test Lesson Watched Event
        $lesson = Lesson::factory()->create();
        event(new LessonWatched($lesson, $user));

        // Assert: Check if LessonWatched event is fired with the correct payload
        Event::assertDispatched(LessonWatched::class, function ($event) use ($lesson, $user) {
            return $event->lesson->id === $lesson->id && $event->user->id === $user->id;
        });

        // Test Comment Written Event
        $newComment = Comment::factory()->userComment($user)->create();
        event(new CommentWritten($newComment));

        // Assert: Check if CommentWritten event is fired with the correct payload
        Event::assertDispatched(CommentWritten::class, function ($event) use ($user, $newComment) {
            return $event->comment->id === $newComment->id && $event->comment->user_id === $user->id;
        });

        // Test Achievement Unlock Event
        event(new AchievementUnlocked('5 Lessons Watched', $user));
        // Assert: Check if AchievementUnlocked event is fired with the correct payload
        Event::assertDispatched(AchievementUnlocked::class, function ($event) use ($user) {
            return $event->achievement_name === '5 Lessons Watched' && $event->user->id === $user->id;
        });

        // Test Badge Unlock Event
        event(new BadgeUnlocked('Intermediate: 4 Achievements', $user));
        // Assert: Check if BadgeUnlocked event is fired with the correct payload
        Event::assertDispatched(BadgeUnlocked::class, function ($event) use ($user) {
            return $event->badge_name === 'Intermediate: 4 Achievements' && $event->user->id === $user->id;
        });
    }

    /** @test */
    public function test_the_application_with_multiple_achievements_returns_a_successful_response(): void
    {
        Event::fake();
        $achievementService = new AchievementService();
        // Arrange: Create a user and initialize necessary variables
        $user = User::factory()->create();
        // $achievementService = new AchievementService();
        $lessons = Lesson::factory()->count(5)->create();
        for ($i = 0; $i < count($lessons); $i++) {
            // Dispatch LessonWatched event for the user

            // Create an instance of the LessonWatched event
            $lessonWatchedEvent = new LessonWatched($lessons[$i], $user);

            // Create an instance of the LessonWatchedListener
            $lessonWatchedListener = new LessonWatchedListener($achievementService);

            // Call the handle method manually
            $lessonWatchedListener->handle($lessonWatchedEvent);
            event(new LessonWatched($lessons[$i], $user));

            // Assert that LessonWatched event is fired
            Event::assertDispatched(LessonWatched::class, function ($event) use ($lessons, $user, $i) {
                return $event->lesson->id === $lessons[$i]->id && $event->user->id === $user->id;
            });
        }
        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'unlocked_achievements',
            'next_available_achievements',
            'current_badge',
            'next_badge',
            'remaining_to_unlock_next_badge',
        ]);
        $response->assertJson([
            'unlocked_achievements' => [
                'First Lesson Watched',
                '5 Lessons Watched',
            ],
            'next_available_achievements' => [
                '10 Lessons Watched',
                'First Comment Written',
            ],
            'current_badge' => 'Intermediate: 4 Achievements',
            'next_badge' => 'Advanced: 8 Achievement',
            'remaining_to_unlock_next_badge' => 3
        ]);
    }

    /** @test */
    public function test_user_unlocks_first_lesson_watched_achievement_using_service()
    {
        Event::fake();
        $user = $this->user;
        $achievementService = new AchievementService();


        // Create an instance of the LessonWatched event
        $lessonWatchedEvent = new LessonWatched($this->lessons[0], $user);

        // Create an instance of the LessonWatchedListener
        $lessonWatchedListener = new LessonWatchedListener($achievementService);

        // Call the handle method manually
        $lessonWatchedListener->handle($lessonWatchedEvent);

        // Dispatch LessonWatched event for the user
        event(new LessonWatched($this->lessons[0], $user));

        // Assert that LessonWatched event is fired
        Event::assertDispatched(LessonWatched::class, function ($event) use ($user) {
            return $event->lesson->id === $this->lessons[0]->id && $event->user->id === $user->id;
        });

        $unlockedAchievements = $achievementService->getUnlockedAchievements($user);

        // Check achievements endpoint response using the service
        $unlockedAchievements = $achievementService->getUnlockedAchievements($user);
        $nextAvailableAchievements = $achievementService->getNextAvailableAchievements($user);
        $currentBadge = $achievementService->getCurrentBadge($user);
        $nextBadge = $achievementService->getNextBadge($user);
        $remainingToUnlockNextBadge = $achievementService->getRemainingToUnlockNextBadge($user);

        // Assertions for achievements
        $this->assertEquals('First Lesson Watched', $unlockedAchievements[0]);
        $this->assertEquals('5 Lessons Watched', $nextAvailableAchievements[0]);

        // Assertions for badges
        $this->assertEquals('Beginner: 0 Achievements', $currentBadge);
        $this->assertEquals('Intermediate: 4 Achievements', $nextBadge);
        $this->assertEquals(3, $remainingToUnlockNextBadge);
    }

    /** @test */
    public function test_user_unlocks_first_comment_written_achievement_using_service()
    {
        Event::fake();
        $user = $this->user;
        $achievementService = new AchievementService();

        // Create a comment associated with the user
        $newComment = Comment::factory()->userComment($user)->create();

        // Dispatch CommentWritten event for the user
        event(new CommentWritten($newComment));

        // Assert that CommentWritten event is fired
        Event::assertDispatched(CommentWritten::class, function ($event) use ($newComment, $user) {
            return $event->comment->id === $newComment->id && $event->comment->user_id === $user->id;
        });

        $unlockedAchievements = $achievementService->getUnlockedAchievements($user);

        // Check achievements endpoint response using the service
        $unlockedAchievements = $achievementService->getUnlockedAchievements($user);
        $nextAvailableAchievements = $achievementService->getNextAvailableAchievements($user);
        $currentBadge = $achievementService->getCurrentBadge($user);
        $nextBadge = $achievementService->getNextBadge($user);
        $remainingToUnlockNextBadge = $achievementService->getRemainingToUnlockNextBadge($user);

        // Assertions for achievements
        $this->assertEquals('First Comment Written', $unlockedAchievements[0]);
        $this->assertEquals('First Lesson Watched', $nextAvailableAchievements[0]);
        $this->assertEquals('3 Comments Written', $nextAvailableAchievements[1]);

        // Assertions for badges
        $this->assertEquals('Beginner: 0 Achievements', $currentBadge);
        $this->assertEquals('Intermediate: 4 Achievements', $nextBadge);
        $this->assertEquals(3, $remainingToUnlockNextBadge);
    }

    /** @test */
    public function test_user_unlocks_new_achievement()
    {
        Event::fake();
        $achievementService = new AchievementService();
        // Arrange: Create a user and initialize necessary variables
        $user = User::factory()->create();
        // $achievementService = new AchievementService();
        $lessons = Lesson::factory()->count(5)->create();
        for ($i = 0; $i < count($lessons); $i++) {
            // Dispatch LessonWatched event for the user

            // Create an instance of the LessonWatched event
            $lessonWatchedEvent = new LessonWatched($lessons[$i], $user);

            // Create an instance of the LessonWatchedListener
            $lessonWatchedListener = new LessonWatchedListener($achievementService);

            // Call the handle method manually
            $lessonWatchedListener->handle($lessonWatchedEvent);
            event(new LessonWatched($lessons[$i], $user));

            // Assert that LessonWatched event is fired
            Event::assertDispatched(LessonWatched::class, function ($event) use ($lessons, $user, $i) {
                return $event->lesson->id === $lessons[$i]->id && $event->user->id === $user->id;
            });
        }

        // Assert: Check if AchievementUnlocked event is fired with the correct payload
        Event::assertDispatched(AchievementUnlocked::class, function ($event) use ($user) {
            return $event->achievement_name === '5 Lessons Watched' && $event->user->id === $user->id;
        });
    }

    // /** @test */
    public function test_user_unlocks_new_badge()
    {
        Event::fake();
        // Arrange: Create a user and initialize necessary variables
        $user = User::factory()->create();
        $achievementService = new AchievementService();
        // $achievementService = new AchievementService();

        // $achievementService = new AchievementService();
        $lessons = Lesson::factory()->count(5)->create();
        for ($i = 0; $i < count($lessons); $i++) {
            // Dispatch LessonWatched event for the user

            // Create an instance of the LessonWatched event
            $lessonWatchedEvent = new LessonWatched($lessons[$i], $user);

            // Create an instance of the LessonWatchedListener
            $lessonWatchedListener = new LessonWatchedListener($achievementService);

            // Call the handle method manually
            $lessonWatchedListener->handle($lessonWatchedEvent);
            // Call the handle method with fake event
            event(new LessonWatched($lessons[$i], $user));

            // Assert that LessonWatched event is fired
            Event::assertDispatched(LessonWatched::class, function ($event) use ($lessons, $user, $i) {
                return $event->lesson->id === $lessons[$i]->id && $event->user->id === $user->id;
            });
        }


        for ($i = 0; $i < 5; $i++) {
            // Create a comment associated with the user
            $newComment = Comment::factory()->userComment($user)->create();
            // Dispatch CommentWritten event for the user

            // Create an instance of the CommentWritten event
            $commentWrittenEvent = new CommentWritten($newComment, $user);

            // Create an instance of the CommentWrittenListener
            $commentWrittenListener = new CommentWrittenListener($achievementService);

            // Call the handle method manually
            $commentWrittenListener->handle($commentWrittenEvent);
            event(new CommentWritten($newComment));

            // Assert that CommentWritten event is fired
            Event::assertDispatched(CommentWritten::class, function ($event) use ($user, $newComment, $i) {
                return $event->comment->id === $newComment->id && $event->comment->user_id === $user->id;
            });
        }

        // Assert: Check if BadgeUnlocked event is fired with the correct payload
        Event::assertDispatched(BadgeUnlocked::class, function ($event) use ($user) {
            return $event->badge_name === 'Intermediate: 4 Achievements' && $event->user->id === $user->id;
        });
    }

}
