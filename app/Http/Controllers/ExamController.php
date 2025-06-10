<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;

use App\Models\Product;
use App\Repositories\Eloquents\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ExamController extends Controller
{
    public function selectCourse()
    {

        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->withTrashed()
            ->latest('id')
            ->get();

        return view('exam.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Course $course)
    {
        return view('exam.index', [
            'exams' => ExamRepository::query()->where('course_id', '=', $course->id)->latest('id')->get(),
            'course' => $course
        ]);
    }

    /**
     * Get all exams for a specific course, including multi_chance for each exam.
     *
     * Example JSON response:
     * {
     *   "course": {
     *     "id": 1,
     *     "title": "Math 101",
     *     ...
     *   },
     *   "exams": [
     *     {
     *       "id": 5,
     *       "title": "Final Exam",
     *       "multi_chance": true,
     *       ...
     *     },
     *     {
     *       "id": 6,
     *       "title": "Midterm",
     *       "multi_chance": false,
     *       ...
     *     }
     *   ]
     * }
     */
    public function getExamByCourseId($id)
    {
       $product = Course::with(['exams'])->find($id);
       // Ensure multi_chance is included in each exam
       $exams = $product->exams->map(function($exam) {
           return $exam->toArray();
       });
       return response()->json([
           'course' => $product,
           'exams' => $exams
       ]);
    }

    public function create(Course $course)
    {
        return view('exam.create', [
            'selectedCourse' => $course,
            'courses' => CourseRepository::query()->get(),
        ]);
    }

    public function show($id){

        $exam = Exam::with('questions')->find($id);



        return response()->json(['exam'=>$exam]);
    }

    public function store(Request $request)
    {
        $exam = ExamRepository::storeByRequest($request);

        // Check if the exam has at least 3 questions
        $examWithQuestions = $exam->load('questions');
        if ($examWithQuestions->questions->count() < 3) {
            // Optionally, you could delete the exam if created without enough questions
            // $exam->delete();
            return response()->json([
                'error' => 'An exam must have at least 3 questions.'
            ], 422);
        }

        //        NotifyEvent::dispatch(NotificationTypeEnum::NewExamFromCourse, [
        //            'course_id' => $exam->course_id
        //        ]);

        return response()->json(['exam' => $examWithQuestions], 201);
    }

    public function edit(Exam $exam)
    {
        return view('exam.edit', [
            'exam' => $exam,
            'courses' => CourseRepository::query()->paginate(12),
        ]);
    }

    public function update(Request $request,   $exam)
    {
          $exam = Exam::find($exam);

        ExamRepository::updateByRequest($request, $exam);

        return response()->json([]);
    }

    public function destroy($id)
    {
        $exam = Exam::find($id);
        $courseId = $exam->course_id;

        $exam->delete();

        return response()->json([]);
    }
}
