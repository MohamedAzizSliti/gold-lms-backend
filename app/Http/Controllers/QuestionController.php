<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Enums\QuestionTypeEnum;

class QuestionController extends Controller
{
    /**
     * Store a newly created question in storage.
     * Supports types: single_choice, multiple_choice, binary (yes/no), or written response.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'nullable|integer|exists:exams,id',
            'quiz_id' => 'nullable|integer|exists:quizzes,id',
            'type' => 'required|string|in:single_choice,multiple_choice,binary,write',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'options.*.text' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        // Enforce: questions for quizzes must be multiple_choice only
        if (!empty($data['quiz_id']) && $data['type'] !== 'multiple_choice') {
            return response()->json([
                'error' => 'Quiz questions must be of type multiple_choice.'
            ], 422);
        }
        // Enforce: must attach to either exam or quiz, not both or neither
        if ((empty($data['exam_id']) && empty($data['quiz_id'])) || (!empty($data['exam_id']) && !empty($data['quiz_id']))) {
            return response()->json([
                'error' => 'A question must be attached to either an exam or a quiz, but not both.'
            ], 422);
        }
        // Map 'write' to null options
        if ($data['type'] === 'write') {
            $data['options'] = null;
        }

        $question = Question::create([
            'exam_id' => $data['exam_id'] ?? null,
            'quiz_id' => $data['quiz_id'] ?? null,
            'type' => $data['type'],
            'question_text' => $data['question_text'],
            'options' => $data['options'] ?? null,
        ]);

        return response()->json(['question' => $question], 201);
    }

    /**
     * Example JSON for creating a question:
     *
     * Single choice:
     * {
     *   "exam_id": 1,
     *   "type": "single_choice",
     *   "question_text": "What is 2+2?",
     *   "options": [
     *     {"text": "3", "is_correct": false},
     *     {"text": "4", "is_correct": true},
     *     {"text": "5", "is_correct": false}
     *   ]
     * }
     *
     * Multiple choice:
     * {
     *   "exam_id": 1,
     *   "type": "multiple_choice",
     *   "question_text": "Which are prime numbers?",
     *   "options": [
     *     {"text": "2", "is_correct": true},
     *     {"text": "4", "is_correct": false},
     *     {"text": "5", "is_correct": true}
     *   ]
     * }
     *
     * Binary (yes/no):
     * {
     *   "exam_id": 1,
     *   "type": "binary",
     *   "question_text": "Is the sky blue?",
     *   "options": [
     *     {"text": "Yes", "is_correct": true},
     *     {"text": "No", "is_correct": false}
     *   ]
     * }
     *
     * Written response:
     * {
     *   "exam_id": 1,
     *   "type": "write",
     *   "question_text": "Explain gravity.",
     *   "options": null
     * }
     */
}
