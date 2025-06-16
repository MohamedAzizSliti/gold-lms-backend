<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSession;
use App\Models\Enrollment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizSessionController extends Controller
{
    /**
     * Start a new quiz session
     */
    public function start(Request $request)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
            ]);
            
            $user_id = Auth::id();
            
            \Log::info('🎯 Quiz session start attempt', [
                'user_id' => $user_id,
                'quiz_id' => $request->quiz_id,
                'request_data' => $request->all()
            ]);
            
            if (!$user_id) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $quiz = Quiz::findOrFail($request->quiz_id);
            
            \Log::info('✅ Quiz found', [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'course_id' => $quiz->course_id
            ]);
            
            // TEMPORARY: Skip all enrollment checks and complex logic
            // Just try to create a basic session
            
            // Find or create an enrollment for this user and course
            $enrollment = Enrollment::where('user_id', $user_id)
                ->where('course_id', $quiz->course_id)
                ->first();
                
            if (!$enrollment) {
                \Log::info('🔄 Creating temporary enrollment for quiz session', [
                    'user_id' => $user_id,
                    'course_id' => $quiz->course_id
                ]);
                
                // Create a temporary enrollment to satisfy the foreign key constraint
                $enrollment = Enrollment::create([
                    'user_id' => $user_id,
                    'course_id' => $quiz->course_id,
                    'status' => 'active',
                    'progress' => 0,
                    'enrolled_at' => now(),
                    'price_paid' => 0
                ]);
            }
            
            \Log::info('✅ Using enrollment', [
                'enrollment_id' => $enrollment->id,
                'enrollment_status' => $enrollment->status
            ]);
            
            // Check if there's an ongoing session
            $existingSession = QuizSession::where('user_id', $user_id)
                ->where('quiz_id', $quiz->id)
                ->where('status', 'ongoing')
                ->first();
                
            if ($existingSession) {
                \Log::info('✅ Found existing ongoing session', [
                    'session_id' => $existingSession->id
                ]);
                
                return response()->json([
                    'message' => 'You have an ongoing quiz session',
                    'quiz_session' => $existingSession,
                    'time_left' => $quiz->duration,
                    'questions' => $quiz->questions ?? [],
                ]);
            }
            
            // Create new session with minimal data
            $sessionData = [
                'user_id' => $user_id,
                'quiz_id' => $quiz->id,
                'enrollment_id' => $enrollment->id,
                'started_at' => now(),
                'status' => 'ongoing'
            ];
            
            \Log::info('🔄 Attempting to create quiz session', [
                'session_data' => $sessionData
            ]);
            
            $quizSession = QuizSession::create($sessionData);
            
            \Log::info('✅ Quiz session created successfully', [
                'session_id' => $quizSession->id,
                'user_id' => $user_id,
                'quiz_id' => $quiz->id
            ]);
            
            return response()->json([
                'message' => 'Quiz session started',
                'quiz_session' => $quizSession,
                'time_left' => $quiz->duration,
                'questions' => $quiz->questions ?? [],
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('❌ Quiz session start error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'quiz_id' => $request->quiz_id ?? null
            ]);
            
            return response()->json([
                'message' => 'Failed to start quiz session: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit answers for a quiz
     */
    public function submit(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_option' => 'required',
        ]);
        
        $quizSession = QuizSession::findOrFail($sessionId);
        
        // Check if user owns this session
        if ($quizSession->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Check if session is still ongoing
        if ($quizSession->status !== 'ongoing') {
            return response()->json(['message' => 'This quiz session is already completed'], 422);
        }
        
        // Check if time has expired (if quiz has a duration)
        $quiz = Quiz::findOrFail($quizSession->quiz_id);
        if ($quiz->duration) {
            $timePassed = now()->diffInMinutes($quizSession->started_at);
            
            if ($timePassed > $quiz->duration) {
                $quizSession->status = 'expired';
                $quizSession->submitted_at = now();
                $quizSession->save();
                
                return response()->json([
                    'message' => 'Quiz time has expired',
                    'quiz_session' => $quizSession
                ], 422);
            }
        }
        
        // Process answers
        $totalQuestions = 0;
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $answerDetails = [];
        
        DB::transaction(function() use ($request, $quizSession, &$totalQuestions, &$correctAnswers, &$wrongAnswers, &$answerDetails) {
            foreach ($request->answers as $answer) {
                $question = Question::findOrFail($answer['question_id']);
                $totalQuestions++;
                
                // Get correct answer
                $options = json_decode($question->options, true);
                $correctOption = null;
                
                foreach ($options as $key => $option) {
                    if (isset($option['is_correct']) && $option['is_correct']) {
                        $correctOption = $key;
                        break;
                    }
                }
                
                $isCorrect = ($correctOption == $answer['selected_option']);
                
                if ($isCorrect) {
                    $correctAnswers++;
                } else {
                    $wrongAnswers++;
                }
                
                // Store the answer
                $quizSession->answers()->create([
                    'question_id' => $answer['question_id'],
                    'selected_option' => $answer['selected_option'],
                    'is_correct' => $isCorrect,
                ]);
                
                $answerDetails[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->text,
                    'selected_option' => $answer['selected_option'],
                    'correct_option' => $correctOption,
                    'is_correct' => $isCorrect,
                ];
            }
            
            // Calculate score - for quizzes we usually show the score right away
            $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
            $isPassing = $score >= ($quiz->pass_percentage ?? 70); // Default pass percentage of 70%
            
            // Update session
            $quizSession->status = 'completed';
            $quizSession->submitted_at = now();
            $quizSession->score = $score;
            $quizSession->correct_answers = $correctAnswers;
            $quizSession->wrong_answers = $wrongAnswers;
            $quizSession->passed = $isPassing;
            $quizSession->save();
        });
        
        return response()->json([
            'message' => 'Quiz submitted successfully',
            'quiz_session' => $quizSession,
            'score' => $quizSession->score,
            'passed' => $quizSession->passed,
            'answer_details' => $answerDetails
        ]);
    }
    
    /**
     * Get quiz results
     */
    public function results($sessionId)
    {
        $quizSession = QuizSession::with(['answers', 'quiz', 'user'])->findOrFail($sessionId);
        
        // Check if user owns this session or is an instructor
        if (Auth::id() !== $quizSession->user_id && 
            Auth::id() !== $quizSession->quiz->course->instructor_id && 
            !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'quiz_session' => $quizSession,
            'answers' => $quizSession->answers->map(function ($answer) {
                $question = Question::find($answer->question_id);
                
                return [
                    'question' => $question,
                    'selected_option' => $answer->selected_option,
                    'is_correct' => $answer->is_correct,
                    'explanation' => $question->explanation ?? null,
                ];
            }),
        ]);
    }
    
    /**
     * List user's quiz sessions
     */
    public function userSessions(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        // Check permissions (only own sessions or admin/instructor)
        if (Auth::id() !== $user_id && !Auth::user()->hasAnyRole(['admin', 'instructor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $quizSessions = QuizSession::with(['quiz', 'quiz.course'])
            ->where('user_id', $user_id)
            ->latest('created_at')
            ->paginate($request->per_page ?? 10);
            
        return response()->json($quizSessions);
    }
    
    /**
     * List all sessions for a quiz (instructor/admin only)
     */
    public function quizSessions(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        
        // Check permissions (only instructor of the course or admin)
        if (Auth::id() !== $quiz->course->instructor_id && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $sessions = QuizSession::with(['user'])
            ->where('quiz_id', $quizId)
            ->latest('created_at')
            ->paginate($request->per_page ?? 10);
            
        return response()->json($sessions);
    }
}
