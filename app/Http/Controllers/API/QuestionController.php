<?php

namespace App\Http\Controllers\API;

use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::query();
        
        // Filter by quiz_id or exam_id
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        } elseif ($request->has('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        
        $questions = $query->latest('created_at')->paginate($request->paginate ?? 15);
        return response()->json($questions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => ['required', new Enum(QuestionTypeEnum::class)],
            'options' => 'required|json',
            'quiz_id' => 'required_without:exam_id|exists:quizzes,id|nullable',
            'exam_id' => 'required_without:quiz_id|exists:exams,id|nullable',
            'course_id' => 'required|exists:courses,id',
            'order' => 'integer|nullable',
        ]);
        
        // Check that at least one correct option is provided
        $options = json_decode($validated['options'], true);
        $hasCorrectOption = false;
        foreach ($options as $option) {
            if (isset($option['is_correct']) && $option['is_correct']) {
                $hasCorrectOption = true;
                break;
            }
        }
        
        if (!$hasCorrectOption) {
            return response()->json(['message' => 'At least one correct option is required'], 422);
        }
        
        $question = Question::create($validated);
        
        // If this is for a quiz, update the quiz
        if ($request->has('quiz_id')) {
            $quiz = Quiz::findOrFail($request->quiz_id);
            // Add any quiz-specific logic here
        } 
        // If this is for an exam, update the exam
        elseif ($request->has('exam_id')) {
            $exam = Exam::findOrFail($request->exam_id);
            // Add any exam-specific logic here
        }
        
        return response()->json(['question' => $question], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $question = Question::findOrFail($id);
        return response()->json(['question' => $question]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        
        $validated = $request->validate([
            'text' => 'sometimes|required|string',
            'type' => ['sometimes', 'required', new Enum(QuestionTypeEnum::class)],
            'options' => 'sometimes|required|json',
            'quiz_id' => 'sometimes|required_without:exam_id|exists:quizzes,id|nullable',
            'exam_id' => 'sometimes|required_without:quiz_id|exists:exams,id|nullable',
            'course_id' => 'sometimes|required|exists:courses,id',
            'order' => 'integer|nullable',
        ]);
        
        // If options are being updated, check for at least one correct option
        if ($request->has('options')) {
            $options = json_decode($validated['options'], true);
            $hasCorrectOption = false;
            foreach ($options as $option) {
                if (isset($option['is_correct']) && $option['is_correct']) {
                    $hasCorrectOption = true;
                    break;
                }
            }
            
            if (!$hasCorrectOption) {
                return response()->json(['message' => 'At least one correct option is required'], 422);
            }
        }
        
        $question->update($validated);
        
        return response()->json(['question' => $question]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully']);
    }
    
    /**
     * Bulk store questions for a quiz or exam.
     */
    public function bulkStore(Request $request)
    {
        // Validate main fields
        $request->validate([
            'questions' => 'required|array|min:3', // Require at least 3 questions
            'questions.*.text' => 'required|string',
            'questions.*.type' => ['required', new Enum(QuestionTypeEnum::class)],
            'questions.*.options' => 'required|json',
            'quiz_id' => 'required_without:exam_id|exists:quizzes,id|nullable',
            'exam_id' => 'required_without:quiz_id|exists:exams,id|nullable',
            'course_id' => 'required|exists:courses,id',
        ]);
        
        $createdQuestions = [];
        $questionParent = null;
        
        // Determine if we're working with quiz or exam
        if ($request->has('quiz_id')) {
            $questionParent = Quiz::findOrFail($request->quiz_id);
        } elseif ($request->has('exam_id')) {
            $questionParent = Exam::findOrFail($request->exam_id);
        } else {
            return response()->json(['message' => 'Either quiz_id or exam_id is required'], 422);
        }
        
        // Process each question
        foreach ($request->questions as $index => $questionData) {
            // Ensure options has at least one correct answer
            $options = json_decode($questionData['options'], true);
            $hasCorrectOption = false;
            foreach ($options as $option) {
                if (isset($option['is_correct']) && $option['is_correct']) {
                    $hasCorrectOption = true;
                    break;
                }
            }
            
            if (!$hasCorrectOption) {
                return response()->json([
                    'message' => 'Question #' . ($index + 1) . ' does not have a correct option'
                ], 422);
            }
            
            // Create the question
            $question = new Question([
                'text' => $questionData['text'],
                'type' => $questionData['type'],
                'options' => $questionData['options'],
                'course_id' => $request->course_id,
                'order' => $index + 1,
            ]);
            
            // Assign to quiz or exam
            if ($request->has('quiz_id')) {
                $question->quiz_id = $request->quiz_id;
            } else {
                $question->exam_id = $request->exam_id;
            }
            
            $question->save();
            $createdQuestions[] = $question;
        }
        
        return response()->json([
            'message' => 'Questions created successfully',
            'questions' => $createdQuestions
        ], 201);
    }
}
