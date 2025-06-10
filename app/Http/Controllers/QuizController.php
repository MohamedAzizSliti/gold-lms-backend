<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $quizzes = Quiz::latest('created_at')->paginate($request->paginate ?? Quiz::count());
        return response()->json($quizzes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $quiz = Quiz::create($request->all());
        return response()->json(['quiz' => $quiz], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $quiz = Quiz::with('questions')->find($id);
        return response()->json(['quiz' => $quiz]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($request->all());
        return response()->json(['quiz' => $quiz]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    /**
     * Get all quizzes for a specific course.
     */
    public function getQuizByCourseId($id)
    {
        $quizzes = Quiz::with('questions')->where('course_id', $id)->get();
        return response()->json(['quizzes' => $quizzes]);
    }
}
