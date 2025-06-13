<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $exams = Exam::latest('created_at')->paginate($request->paginate ?? Exam::count());
        return response()->json($exams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'duration' => 'nullable|integer', // in minutes
            'pass_percentage' => 'required|numeric|min:0|max:100',
            'multi_chance' => 'boolean',
            'status' => 'boolean',
        ]);

        $exam = Exam::create($validated);
        return response()->json(['exam' => $exam], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $exam = Exam::with('questions')->find($id);
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }
        return response()->json(['exam' => $exam]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'sometimes|required|exists:courses,id',
            'duration' => 'nullable|integer', // in minutes
            'pass_percentage' => 'sometimes|required|numeric|min:0|max:100',
            'multi_chance' => 'boolean',
            'status' => 'boolean',
        ]);

        $exam->update($validated);
        return response()->json(['exam' => $exam]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        $exam->delete();
        return response()->json(['message' => 'Exam deleted successfully']);
    }

    /**
     * Get all exams for a specific course.
     */
    public function getExamByCourseId($id)
    {
        $exams = Exam::with('questions')->where('course_id', $id)->get();
        return response()->json(['exams' => $exams]);
    }
    
    /**
     * Replicate an existing exam.
     */
    public function replicate(Request $request)
    {
        $exam = Exam::findOrFail($request->id);
        $newExam = $exam->replicate();
        $newExam->title = $request->title ?? $exam->title . ' (Copy)';
        $newExam->save();
        
        // Replicate the questions if needed
        if ($request->with_questions) {
            foreach ($exam->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->exam_id = $newExam->id;
                $newQuestion->save();
            }
        }
        
        return response()->json(['exam' => $newExam->load('questions')]);
    }
    
    /**
     * Change the status of an exam.
     */
    public function status($id, $status)
    {
        $exam = Exam::findOrFail($id);
        $exam->status = $status === 'true' || $status === '1';
        $exam->save();
        
        return response()->json(['exam' => $exam]);
    }
    
    /**
     * Delete multiple exams at once.
     */
    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        Exam::whereIn('id', $ids)->delete();
        
        return response()->json(['message' => 'Exams deleted successfully']);
    }
}
