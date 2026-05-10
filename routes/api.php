<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CivicActionController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\CooperationController;
use App\Http\Controllers\Api\CorruptionController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\LiveController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\MuseumController;
use App\Http\Controllers\Api\PublicMediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    // ── Santé de l'API ────────────────────────────────────────────────────
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'message' => 'API VWAJEN opérationnelle',
        'version' => '1.0',
    ]));

    // Fichiers publics (CORS) — utilisé par l’app au lieu de /storage/ direct
    Route::get('/media/{path}', [PublicMediaController::class, 'serve'])
        ->where('path', '.*')
        ->name('api.v1.media');

    // ── Authentification (public) ─────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ── Profils utilisateurs (public) ────────────────────────────────────
    Route::get('/users/{username}', [AuthController::class, 'getUserProfile']);
    Route::get('/users/{username}/followers', [AuthController::class, 'getFollowers']);
    Route::get('/users/{username}/following', [AuthController::class, 'getFollowing']);
    Route::get('/users/search', [AuthController::class, 'searchUsers']);

    // ── Éducation (public) ────────────────────────────────────────────────
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::get('/{id}', [CourseController::class, 'show']);
        Route::get('/{courseId}/lessons', [CourseController::class, 'lessons']);
        Route::get('/{courseId}/quiz', [CourseController::class, 'getQuiz']);
    });

    // ── Feed Vidéos (public) ──────────────────────────────────────────────
    Route::prefix('feed')->group(function () {
        Route::get('/', [FeedController::class, 'index']);
        Route::get('/videos/{id}', [FeedController::class, 'show']);
        Route::get('/videos/{id}/comments', [FeedController::class, 'comments']);
        Route::get('/lives', [FeedController::class, 'lives']);
        Route::get('/lives/scheduled', [FeedController::class, 'scheduledLives']);
    });

    // ── Événements (public) ───────────────────────────────────────────────
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::get('/{id}', [EventController::class, 'show']);
    });
    Route::get('/events-calendar', [EventController::class, 'calendar']);

    // ── Actions citoyennes (public) ───────────────────────────────────────
    Route::prefix('actions')->group(function () {
        Route::get('/', [CivicActionController::class, 'index']);
        Route::get('/map', [CivicActionController::class, 'map']);
        Route::get('/{id}', [CivicActionController::class, 'show']);
    });

    // ── Publications (public) ─────────────────────────────────────────────
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::get('/{id}/comments', [PostController::class, 'getComments']);
    });

    // ── Votes — Démocratie participative (public) ─────────────────────────
    Route::prefix('votes')->group(function () {
        Route::get('/', [VoteController::class, 'index']);
        Route::get('/{id}', [VoteController::class, 'show']);
    });

    // ── Lives — chat public ───────────────────────────────────────────────
    Route::get('/lives/{id}/messages', [LiveController::class, 'getMessages']);

    // ── Projets citoyens (public) ─────────────────────────────────────────
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/{id}', [ProjectController::class, 'show']);
        Route::get('/{id}/comments', [ProjectController::class, 'getComments']);
    });

    // ── Coopération Afrique-Haïti (public) ───────────────────────────────
    Route::prefix('cooperation')->group(function () {
        Route::get('/', [CooperationController::class, 'index']);
        Route::get('/{id}', [CooperationController::class, 'show']);
    });

    // ── Communication & Publicités (public) ───────────────────────────────
    Route::get('/communication/ads', [CommunicationController::class, 'index']);

    // ── Musée des Révolutionnaires (public) ───────────────────────────────
    Route::prefix('museum')->group(function () {
        Route::get('/', [MuseumController::class, 'index']);
        Route::get('/featured', [MuseumController::class, 'featured']);
        Route::get('/categories', [MuseumController::class, 'categories']);
        Route::get('/{id}', [MuseumController::class, 'show']);
        Route::get('/slug/{slug}', [MuseumController::class, 'showBySlug']);
    });

    // ── Dénonciation Corruption (ANONYME) ────────────────────────────────
    Route::prefix('corruption')->group(function () {
        Route::post('/', [CorruptionController::class, 'store']);
        Route::post('/track', [CorruptionController::class, 'track']);
        Route::get('/', [CorruptionController::class, 'index']);
        Route::get('/stats', [CorruptionController::class, 'stats']);
    });

    // ═══════════════════════════════════════════════════════════════════════
    // Routes AUTHENTIFIÉES
    // ═══════════════════════════════════════════════════════════════════════
    Route::middleware('auth:sanctum')->group(function (): void {

        // ── Compte & Profil ───────────────────────────────────────────────
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'updatePassword']);
            Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
        });

        // ── Réseau social (follow/unfollow) ───────────────────────────────
        Route::post('/users/{username}/follow', [AuthController::class, 'follow']);

        // ── Éducation ─────────────────────────────────────────────────────
        Route::prefix('courses')->group(function () {
            Route::post('/{id}/enroll', [CourseController::class, 'enroll']);
            Route::get('/{courseId}/lessons/{lessonId}', [CourseController::class, 'lessonDetail']);
            Route::post('/{courseId}/lessons/{lessonId}/complete', [CourseController::class, 'completeLesson']);
            Route::post('/{courseId}/quiz/submit', [CourseController::class, 'submitQuiz']);
        });
        Route::get('/my/courses', [CourseController::class, 'myCourses']);

        // ── Feed & Vidéos ─────────────────────────────────────────────────
        Route::get('/feed/following', [FeedController::class, 'following']);
        Route::post('/feed/videos/{id}/like', [FeedController::class, 'like']);
        Route::post('/feed/videos/{id}/comment', [FeedController::class, 'comment']);
        Route::post('/feed/videos/{id}/share', [FeedController::class, 'share']);

        // Upload de vidéos courtes
        Route::post('/videos', [VideoController::class, 'store']);
        Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
        Route::get('/my/videos', [VideoController::class, 'myVideos']);

        // ── Lives ─────────────────────────────────────────────────────────
        Route::prefix('lives')->group(function () {
            Route::post('/', [LiveController::class, 'store']);
            Route::get('/{id}', [LiveController::class, 'show']);
            Route::post('/{id}/start', [LiveController::class, 'start']);
            Route::post('/{id}/end', [LiveController::class, 'end']);
            Route::post('/{id}/join', [LiveController::class, 'join']);
            Route::post('/{id}/leave', [LiveController::class, 'leave']);
            Route::post('/{id}/gift', [LiveController::class, 'sendGift']);
            Route::post('/{id}/message', [LiveController::class, 'sendMessage']);
            Route::post('/{id}/guest-invite', [LiveController::class, 'inviteGuest']);
            Route::get('/{id}/guest-invitations', [LiveController::class, 'listGuestInvitations']);
            Route::post('/{id}/guest-invitations/{inviteId}/respond', [LiveController::class, 'respondGuestInvitation']);
            Route::delete('/{id}/guest-invitations/{inviteId}', [LiveController::class, 'revokeGuestInvitation']);
        });

        // ── Événements ────────────────────────────────────────────────────
        Route::post('/events/{id}/participate', [EventController::class, 'participate']);
        Route::delete('/events/{id}/participation', [EventController::class, 'cancelParticipation']);
        Route::get('/my/events', [EventController::class, 'myEvents']);

        // ── Actions citoyennes ────────────────────────────────────────────
        Route::post('/actions', [CivicActionController::class, 'store']);
        Route::post('/actions/{id}/join', [CivicActionController::class, 'join']);
        Route::delete('/actions/{id}/join', [CivicActionController::class, 'leave']);

        // ── Adhésion GJKA ─────────────────────────────────────────────────
        Route::post('/memberships', [MembershipController::class, 'apply']);
        Route::get('/memberships/me', [MembershipController::class, 'me']);

        // ── Bibliothèque ──────────────────────────────────────────────────
        Route::get('/library', [LibraryController::class, 'index']);
        Route::post('/library/save', [LibraryController::class, 'save']);
        Route::delete('/library/save', [LibraryController::class, 'unsave']);

        // ── Publications ──────────────────────────────────────────────────
        Route::post('/posts', [PostController::class, 'store']);
        Route::post('/posts/{id}/like', [PostController::class, 'like']);
        Route::post('/posts/{id}/comment', [PostController::class, 'comment']);
        Route::post('/posts/{id}/share', [PostController::class, 'share']);

        // ── Votes ─────────────────────────────────────────────────────────
        Route::post('/votes/{id}/vote', [VoteController::class, 'castVote']);

        // ── Projets ───────────────────────────────────────────────────────
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::post('/projects/{id}/support', [ProjectController::class, 'support']);
        Route::get('/projects/{id}/comments', [ProjectController::class, 'getComments']);
        Route::post('/projects/{id}/comment', [ProjectController::class, 'addComment']);

        // ── Coopération Afrique–Haïti (annonces utilisateurs + intérêt) ────
        Route::post('/cooperation', [CooperationController::class, 'store']);
        Route::post('/cooperation/{id}/interest', [CooperationController::class, 'expressInterest']);

        // ── Notifications ─────────────────────────────────────────────────
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        // ── Modération & Signalements ─────────────────────────────────────
        Route::post('/moderation/reports', [ModerationController::class, 'store']);
        Route::get('/moderation/reports/mine', [ModerationController::class, 'myReports']);
    });
});
