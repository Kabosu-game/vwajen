<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\Certification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\EngagementPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    // Pôle Éducation - Liste des cours
    public function index(Request $request)
    {
        $courses = Course::where('is_published', true)
            ->with(['category:id,name,color', 'creator:id,name,username,avatar'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->level, fn($q) => $q->where('level', $request->level))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->when($request->featured, fn($q) => $q->where('is_featured', true))
            ->orderBy('is_featured', 'desc')
            ->orderByDesc('enrollments_count')
            ->paginate(12);

        $user = $request->user();
        $enrolledIds = $user ? $user->enrolledCourses()->pluck('courses.id')->toArray() : [];

        $items = collect($courses->items())->map(fn($course) => [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'thumbnail_url' => $course->thumbnail_url,
            'level' => $course->level,
            'type' => $course->type,
            'duration_minutes' => $course->duration_minutes,
            'is_free' => $course->is_free,
            'is_featured' => $course->is_featured,
            'points_reward' => $course->points_reward,
            'enrollments_count' => $course->enrollments_count,
            'lessons_count' => $course->lessons()->count(),
            'category' => $course->category,
            'creator' => $course->creator,
            'is_enrolled' => in_array($course->id, $enrolledIds),
            'created_at' => $course->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $items, 'meta' => [
            'current_page' => $courses->currentPage(),
            'last_page' => $courses->lastPage(),
            'total' => $courses->total(),
        ]]);
    }

    public function show(Request $request, int $id)
    {
        $course = Course::where('is_published', true)
            ->with(['category', 'creator:id,name,username,avatar', 'lessons' => fn($q) => $q->where('is_published', true)])
            ->findOrFail($id);

        $user = $request->user();
        $enrollment = $user ? CourseEnrollment::where(['user_id' => $user->id, 'course_id' => $id])->first() : null;

        return $this->success([
            ...$course->toArray(),
            'is_enrolled' => (bool) $enrollment,
            'progress_percent' => $enrollment?->progress_percent ?? 0,
            'is_completed' => (bool) $enrollment?->completed_at,
            'quizzes_count' => $course->quizzes()->count(),
        ]);
    }

    public function enroll(Request $request, int $id)
    {
        $course = Course::where('is_published', true)->findOrFail($id);
        $user = $request->user();

        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $id],
            ['progress_percent' => 0]
        );

        if (!$enrollment->wasRecentlyCreated) {
            return $this->error('Vous êtes déjà inscrit à ce cours', 409);
        }

        $course->increment('enrollments_count');

        EngagementPoint::create([
            'user_id' => $user->id,
            'points' => 5,
            'action' => 'course_enrolled',
            'pointable_type' => Course::class,
            'pointable_id' => $course->id,
            'description' => 'Inscription au cours: '.$course->title,
        ]);

        return $this->success($enrollment, 'Inscription réussie', 201);
    }

    public function lessons(Request $request, int $courseId)
    {
        $course = Course::where('is_published', true)->findOrFail($courseId);

        $user = $request->user();
        $isEnrolled = $user && CourseEnrollment::where(['user_id' => $user->id, 'course_id' => $courseId])->exists();

        if (!$isEnrolled && !$course->is_free) {
            return $this->error('Inscription requise pour accéder aux leçons', 403);
        }

        $completedIds = $user
            ? LessonProgress::where('user_id', $user->id)->where('is_completed', true)->pluck('lesson_id')->toArray()
            : [];

        $lessons = $course->lessons()
            ->where('is_published', true)
            ->get()
            ->map(fn($lesson) => [
                ...$lesson->toArray(),
                'is_completed' => in_array($lesson->id, $completedIds),
            ]);

        return $this->success(['lessons' => $lessons, 'total' => $lessons->count()]);
    }

    public function lessonDetail(Request $request, int $courseId, int $lessonId)
    {
        $course = Course::where('is_published', true)->findOrFail($courseId);
        $user = $request->user();
        $isEnrolled = $user && CourseEnrollment::where(['user_id' => $user->id, 'course_id' => $courseId])->exists();

        if (!$isEnrolled && !$course->is_free) {
            return $this->error('Inscription requise', 403);
        }

        $lesson = $course->lessons()->where('is_published', true)->findOrFail($lessonId);
        $isCompleted = $user
            ? LessonProgress::where(['user_id' => $user->id, 'lesson_id' => $lessonId, 'is_completed' => true])->exists()
            : false;

        return $this->success([...$lesson->toArray(), 'is_completed' => $isCompleted]);
    }

    public function completeLesson(Request $request, int $courseId, int $lessonId)
    {
        $user = $request->user();

        $enrollment = CourseEnrollment::where(['user_id' => $user->id, 'course_id' => $courseId])->firstOrFail();

        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            ['is_completed' => true, 'completed_at' => now()]
        );

        if ($progress->wasRecentlyCreated || !$progress->is_completed) {
            $progress->update(['is_completed' => true, 'completed_at' => now()]);
        }

        // Recalculer la progression
        $course = Course::find($courseId);
        $totalLessons = $course->lessons()->where('is_published', true)->count();
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->where('is_completed', true)->count();

        $progressPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
        $enrollment->update(['progress_percent' => $progressPercent]);

        // Certification si cours complété
        if ($progressPercent === 100 && !$enrollment->completed_at) {
            $enrollment->update(['completed_at' => now()]);
            $this->issueCertification($user, $course);

            EngagementPoint::create([
                'user_id' => $user->id,
                'points' => $course->points_reward,
                'action' => 'course_completed',
                'pointable_type' => Course::class,
                'pointable_id' => $course->id,
                'description' => 'Cours terminé: '.$course->title,
            ]);
        }

        return $this->success(['progress_percent' => $progressPercent, 'is_completed' => $progressPercent === 100]);
    }

    public function myCourses(Request $request)
    {
        $user = $request->user();

        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course.category', 'course.creator:id,name,username,avatar'])
            ->paginate(10);

        return $this->paginated($enrollments);
    }

    // ── Quiz ──────────────────────────────────────────────────────────────────

    public function getQuiz(Request $request, int $courseId)
    {
        $course = Course::where('is_published', true)->findOrFail($courseId);
        $isEnrolled = $request->user() && CourseEnrollment::where(['user_id' => $request->user()->id, 'course_id' => $courseId])->exists();

        if (!$isEnrolled && !$course->is_free) {
            return $this->error('Inscription requise', 403);
        }

        $quiz = $course->quizzes()->where('is_active', true)->with('questions.answers')->first();

        if (!$quiz) {
            return $this->error('Pa gen quiz pou kou sa a', 404);
        }

        $attempt = $request->user()
            ? QuizAttempt::where(['user_id' => $request->user()->id, 'quiz_id' => $quiz->id])
                ->whereNotNull('completed_at')->latest()->first()
            : null;

        return $this->success([
            'quiz' => [
                'id'                 => $quiz->id,
                'title'              => $quiz->title,
                'description'        => $quiz->description,
                'pass_score'         => $quiz->pass_score,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'questions_count'    => $quiz->questions->count(),
                'questions'          => $quiz->questions->map(fn($q) => [
                    'id'      => $q->id,
                    'text'    => $q->question,
                    'type'    => $q->type,
                    'points'  => $q->points,
                    'answers' => $q->answers->map(fn($a) => ['id' => $a->id, 'text' => $a->answer])->values(),
                ])->values(),
            ],
            'previous_attempt' => $attempt ? [
                'score'     => $attempt->score,
                'max_score' => $attempt->max_score,
                'is_passed' => $attempt->is_passed,
                'completed_at' => $attempt->completed_at?->toISOString(),
            ] : null,
        ]);
    }

    public function submitQuiz(Request $request, int $courseId)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer_id'   => 'required|integer|exists:answers,id',
        ]);

        $user = $request->user();
        $quiz = Quiz::with('questions.answers')->findOrFail($validated['quiz_id']);

        if ($quiz->course_id !== $courseId) {
            return $this->error('Quiz invalide pour ce cours', 422);
        }

        // Calculer le score
        $score = 0;
        $maxScore = 0;

        foreach ($quiz->questions as $question) {
            $maxScore += $question->points;
            $userAnswer = collect($validated['answers'])->firstWhere('question_id', $question->id);
            if ($userAnswer) {
                $correctAnswer = $question->answers->firstWhere('id', $userAnswer['answer_id']);
                if ($correctAnswer && $correctAnswer->is_correct) {
                    $score += $question->points;
                }
            }
        }

        $scorePercent = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        $isPassed = $scorePercent >= $quiz->pass_score;

        $attempt = QuizAttempt::create([
            'user_id'      => $user->id,
            'quiz_id'      => $quiz->id,
            'score'        => $scorePercent,
            'max_score'    => 100,
            'is_passed'    => $isPassed,
            'started_at'   => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        if ($isPassed) {
            $course = Course::find($courseId);
            $this->issueCertification($user, $course);

            EngagementPoint::create([
                'user_id'        => $user->id,
                'points'         => 20,
                'action'         => 'quiz_passed',
                'pointable_type' => Quiz::class,
                'pointable_id'   => $quiz->id,
                'description'    => 'Quiz réussi: ' . $quiz->title,
            ]);
        }

        return $this->success([
            'score'      => $scorePercent,
            'is_passed'  => $isPassed,
            'pass_score' => $quiz->pass_score,
            'points_earned' => $isPassed ? 20 : 0,
        ], $isPassed ? 'Felisitasyon! Ou pase egzamen an!' : 'Pa pase. Eseye ankò.');
    }

    private function issueCertification(object $user, Course $course): void
    {
        Certification::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'certificate_number' => 'VJ-'.strtoupper(Str::random(8)).'-'.date('Y'),
                'issued_at' => now(),
            ]
        );
    }
}
