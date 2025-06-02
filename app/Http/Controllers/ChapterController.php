<?php

namespace App\Http\Controllers;

use App\Enum\NotificationTypeEnum;
use App\Events\NotifyEvent;
use App\Models\Product;
use App\Repositories\Eloquents\ChapterRepository;
use App\Repositories\Eloquents\CourseRepository;
use Illuminate\Http\Request;

use App\Models\Chapter;
use App\Models\Course;
 use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
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

        return view('chapter.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Course $course)
    {
        return view('chapter.index', [
            'chapters' => ChapterRepository::query()->where('course_id', '=', $course->id)->latest('id')->get(),
            'course' => $course
        ]);
    }

    public function create(Course $course)
    {
        return view('chapter.create', [
            'selectedCourse' => $course,
            'courses' => CourseRepository::query()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $chapter = ChapterRepository::storeByRequest($request);

//        NotifyEvent::dispatch(NotificationTypeEnum::NewContentFromCourse, [
//            'course_id' => $chapter->course_id
//        ]);

        return  $this->json('message',['course' => $chapter->course_id]);
    }

    public function getExamByCourseId($id)
    {
        $course = Course::with('chapters')->find($id);

        return response()->json(['course'=> $course,'chapters'=>$course->chapters]);
    }


    public function edit(Chapter $chapter)
    {
        return view('chapter.edit', [
            'chapter' => $chapter,
            'courses' => CourseRepository::query()->paginate(12),
        ]);
    }

    public function update(Request $request, Chapter $chapter)
    {
        $newContent = ChapterRepository::updateByRequest($request, $chapter);

        if ($newContent) {
            NotifyEvent::dispatch(NotificationTypeEnum::NewContentFromCourse, [
                'course_id' => $chapter->course_id
            ]);
        }

        return to_route('chapter.index', ['course' => $chapter->course_id])->withSuccess('Chapter updated');
    }

    public function delete(Chapter $chapter)
    {
        $courseId = $chapter->course_id;
        $chapter->delete();

        return redirect()->route('chapter.index', ['course' => $courseId])->withSuccess('Chapter deleted');
    }
}
