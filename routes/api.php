<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CivicActionController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\CooperationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\LiveController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'message' => 'API VWAJEN opérationnelle',
    ]));

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/users/{username}', [AuthController::class, 'getUserProfile']);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/courses/{courseId}/lessons', [CourseController::class, 'lessons']);

    Route::get('/feed', [FeedController::class, 'index']);
    Route::get('/feed/videos/{id}', [FeedController::class, 'show']);
    Route::get('/feed/videos/{id}/comments', [FeedController::class, 'comments']);
    Route::get('/feed/lives', [FeedController::class, 'lives']);
    Route::get('/feed/lives/scheduled', [FeedController::class, 'scheduledLives']);

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('/events-calendar', [EventController::class, 'calendar']);

    Route::get('/actions', [CivicActionController::class, 'index']);
    Route::get('/actions/map', [CivicActionController::class, 'map']);
    Route::get('/actions/{id}', [CivicActionController::class, 'show']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);

    Route::get('/votes', [VoteController::class, 'index']);
    Route::get('/votes/{id}', [VoteController::class, 'show']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    Route::get('/cooperation', [CooperationController::class, 'index']);
    Route::get('/cooperation/{id}', [CooperationController::class, 'show']);

    Route::get('/communication/ads', [CommunicationController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);

        Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll']);
        Route::get('/courses/{courseId}/lessons/{lessonId}', [CourseController::class, 'lessonDetail']);
        Route::post('/courses/{courseId}/lessons/{lessonId}/complete', [CourseController::class, 'completeLesson']);
        Route::get('/my/courses', [CourseController::class, 'myCourses']);

        Route::get('/feed/following', [FeedController::class, 'following']);
        Route::post('/feed/videos/{id}/like', [FeedController::class, 'like']);
        Route::post('/feed/videos/{id}/comment', [FeedController::class, 'comment']);

        Route::post('/lives', [LiveController::class, 'store']);
        Route::get('/lives/{id}', [LiveController::class, 'show']);
        Route::post('/lives/{id}/start', [LiveController::class, 'start']);
        Route::post('/lives/{id}/end', [LiveController::class, 'end']);
        Route::post('/lives/{id}/join', [LiveController::class, 'join']);
        Route::post('/lives/{id}/leave', [LiveController::class, 'leave']);
        Route::post('/lives/{id}/gift', [LiveController::class, 'sendGift']);

        Route::post('/events/{id}/participate', [EventController::class, 'participate']);
        Route::delete('/events/{id}/participation', [EventController::class, 'cancelParticipation']);
        Route::get('/my/events', [EventController::class, 'myEvents']);

        Route::post('/actions', [CivicActionController::class, 'store']);
        Route::post('/actions/{id}/join', [CivicActionController::class, 'join']);
        Route::delete('/actions/{id}/join', [CivicActionController::class, 'leave']);

        Route::post('/memberships', [MembershipController::class, 'apply']);
        Route::get('/memberships/me', [MembershipController::class, 'me']);

        Route::get('/library', [LibraryController::class, 'index']);
        Route::post('/library/save', [LibraryController::class, 'save']);
        Route::delete('/library/save', [LibraryController::class, 'unsave']);

        Route::post('/posts', [PostController::class, 'store']);
        Route::post('/posts/{id}/like', [PostController::class, 'like']);
        Route::post('/posts/{id}/comment', [PostController::class, 'comment']);

        Route::post('/votes/{id}/vote', [VoteController::class, 'castVote']);

        Route::post('/projects', [ProjectController::class, 'store']);
        Route::post('/projects/{id}/support', [ProjectController::class, 'support']);

        Route::post('/cooperation/{id}/interest', [CooperationController::class, 'expressInterest']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::post('/moderation/reports', [ModerationController::class, 'store']);
        Route::get('/moderation/reports/mine', [ModerationController::class, 'myReports']);
    });
});
