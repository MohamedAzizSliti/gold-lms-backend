<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Enrollment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamSessionController extends Controller
{
    /**
     * Start a new exam session
     */
    public function start(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
            ]);
            
            $user_id = Auth::id();
            
            \Log::info('🎯 Exam session start attempt', [
                'user_id' => $user_id,
                'exam_id' => $request->exam_id,
                'request_data' => $request->all()
            ]);
            
            if (!$user_id) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $exam = Exam::findOrFail($request->exam_id);
            
            \Log::info('✅ Exam found', [
                'exam_id' => $exam->id,
                'exam_title' => $exam->title,
                'course_id' => $exam->course_id
            ]);
            
            // TEMPORARY: Skip all enrollment checks and complex logic
            // Just try to create a basic session
            
            // Find or create an enrollment for this user and course
            $enrollment = Enrollment::where('user_id', $user_id)
                ->where('course_id', $exam->course_id)
                ->first();
                
            if (!$enrollment) {
                \Log::info('🔄 Creating temporary enrollment for exam session', [
                    'user_id' => $user_id,
                    'course_id' => $exam->course_id
                ]);
                
                // Create a temporary enrollment to satisfy the foreign key constraint
                $enrollment = Enrollment::create([
                    'user_id' => $user_id,
                    'course_id' => $exam->course_id,
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
            $existingSession = ExamSession::where('user_id', $user_id)
                ->where('exam_id', $exam->id)
                ->where('status', 'ongoing')
                ->first();
                
            if ($existingSession) {
                \Log::info('✅ Found existing ongoing session', [
                    'session_id' => $existingSession->id
                ]);
                
                $timePassed = now()->diffInMinutes($existingSession->started_at);
                $timeLeft = max(0, ($exam->duration ?? 60) - $timePassed);
                
                return response()->json([
                    'message' => 'You have an ongoing exam session',
                    'exam_session' => $existingSession,
                    'time_left' => $timeLeft,
                    'questions' => $exam->questions ?? [],
                ]);
            }
            
            // Create new session with minimal data
            $sessionData = [
                'user_id' => $user_id,
                'exam_id' => $exam->id,
                'enrollment_id' => $enrollment->id,
                'started_at' => now(),
                'status' => 'ongoing'
            ];
            
            \Log::info('🔄 Attempting to create exam session', [
                'session_data' => $sessionData
            ]);
            
            $examSession = ExamSession::create($sessionData);
            
            \Log::info('✅ Exam session created successfully', [
                'session_id' => $examSession->id,
                'user_id' => $user_id,
                'exam_id' => $exam->id
            ]);
            
            // Format questions to ensure proper JSON encoding
            $formattedQuestions = $exam->questions->map(function ($question) {
                $questionArray = $question->toArray();
                
                // Ensure options is properly formatted as a JSON string, not double-encoded
                if (isset($questionArray['options']) && is_string($questionArray['options'])) {
                    // If it's already a JSON string, keep it as is
                    // Don't re-encode it to avoid double-encoding
                    $questionArray['options'] = $questionArray['options'];
                }
                
                return $questionArray;
            });
            
            return response()->json([
                'message' => 'Exam session started',
                'exam_session' => $examSession,
                'time_left' => $exam->duration,
                'questions' => $formattedQuestions,
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('❌ Exam session start error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'exam_id' => $request->exam_id ?? null
            ]);
            
            return response()->json([
                'message' => 'Failed to start exam session: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit answers for an exam
     */
    public function submit(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_option' => 'required',
        ]);
        
        $examSession = ExamSession::findOrFail($sessionId);
        
        // Check if user owns this session
        if ($examSession->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Check if session is still ongoing
        if ($examSession->status !== 'ongoing') {
            return response()->json(['message' => 'This exam session is already completed'], 422);
        }
        
        // Check if time has expired
        $exam = Exam::findOrFail($examSession->exam_id);
        $timePassed = now()->diffInMinutes($examSession->started_at);
        
        if ($exam->duration && $timePassed > $exam->duration) {
            $examSession->status = 'expired';
            $examSession->submitted_at = now();
            $examSession->save();
            
            return response()->json([
                'message' => 'Exam time has expired',
                'exam_session' => $examSession
            ], 422);
        }
        
        // Process answers
        $totalQuestions = 0;
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $answerDetails = [];
        
        DB::transaction(function() use ($request, $examSession, &$totalQuestions, &$correctAnswers, &$wrongAnswers, &$answerDetails) {
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
                $examSession->answers()->create([
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
            
            // Calculate score
            $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
            $isPassing = $score >= $examSession->exam->pass_percentage;
            
            // Update session
            $examSession->status = 'completed';
            $examSession->submitted_at = now();
            $examSession->score = $score;
            $examSession->correct_answers = $correctAnswers;
            $examSession->wrong_answers = $wrongAnswers;
            $examSession->passed = $isPassing;
            $examSession->save();
        });
        
        return response()->json([
            'message' => 'Exam submitted successfully',
            'exam_session' => $examSession,
            'score' => $examSession->score,
            'passed' => $examSession->passed,
            'answer_details' => $answerDetails
        ]);
    }
    
    /**
     * Get exam results
     */
    public function results($sessionId)
    {
        $examSession = ExamSession::with(['answers', 'exam', 'user'])->findOrFail($sessionId);
        
        // Check if user owns this session or is an instructor
        if (Auth::id() !== $examSession->user_id && 
            Auth::id() !== $examSession->exam->course->instructor_id && 
            !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'exam_session' => $examSession,
            'answers' => $examSession->answers->map(function ($answer) {
                $question = Question::find($answer->question_id);
                
                return [
                    'question' => $question,
                    'selected_option' => $answer->selected_option,
                    'is_correct' => $answer->is_correct,
                ];
            }),
        ]);
    }
    
    /**
     * List user's exam sessions
     */
    public function userSessions(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        // Check permissions (only own sessions or admin/instructor)
        if (Auth::id() !== $user_id && !Auth::user()->hasAnyRole(['admin', 'instructor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $examSessions = ExamSession::with(['exam', 'exam.course'])
            ->where('user_id', $user_id)
            ->latest('created_at')
            ->paginate($request->per_page ?? 10);
            
        return response()->json($examSessions);
    }
    
    /**
     * List all sessions for an exam (instructor/admin only)
     */
    public function examSessions(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);
        
        // Check permissions (only instructor of the course or admin)
        if (Auth::id() !== $exam->course->instructor_id && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $sessions = ExamSession::with(['user'])
            ->where('exam_id', $examId)
            ->latest('created_at')
            ->paginate($request->per_page ?? 10);
            
        return response()->json($sessions);
    }
}
