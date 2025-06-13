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
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);
        
        $user_id = Auth::id();
        $exam = Exam::findOrFail($request->exam_id);
        
        // Check if user is enrolled in the course
        $enrollment = Enrollment::where('user_id', $user_id)
            ->where('course_id', $exam->course_id)
            ->where('status', 'active')
            ->first();
            
        if (!$enrollment) {
            return response()->json([
                'message' => 'You must be enrolled in this course to take the exam'
            ], 403);
        }
        
        // Check if there's an ongoing or completed session
        $existingSession = ExamSession::where('user_id', $user_id)
            ->where('exam_id', $exam->id)
            ->where(function($query) {
                $query->where('status', 'ongoing')
                      ->orWhere('status', 'completed');
            })
            ->first();
            
        // If multi_chance is disabled and there's a completed session, prevent start
        if (!$exam->multi_chance && $existingSession && $existingSession->status === 'completed') {
            return response()->json([
                'message' => 'You have already completed this exam'
            ], 422);
        }
        
        // If there's an ongoing session, return that
        if ($existingSession && $existingSession->status === 'ongoing') {
            $timePassed = now()->diffInMinutes($existingSession->started_at);
            $timeLeft = max(0, ($exam->duration ?? 60) - $timePassed);
            
            return response()->json([
                'message' => 'You have an ongoing exam session',
                'exam_session' => $existingSession,
                'time_left' => $timeLeft,
                'questions' => $exam->questions,
            ]);
        }
        
        // Start new session
        $examSession = ExamSession::create([
            'user_id' => $user_id,
            'exam_id' => $exam->id,
            'started_at' => now(),
            'status' => 'ongoing',
            'enrollment_id' => $enrollment->id,
        ]);
        
        return response()->json([
            'message' => 'Exam session started',
            'exam_session' => $examSession,
            'time_left' => $exam->duration,
            'questions' => $exam->questions,
        ], 201);
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
