<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\RevenueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Teacher Dashboard - Get courses taught by teacher
Route::get('teacher-courses/{id}', function ($id) {
    $courses = \App\Models\Course::with(['media','enrollments.user','category','instructor','exams'])
        ->where('user_id', $id)
        ->get()
        ->map(function($course) {
            // Add cover_image information using the accessor
            $course->cover_image; // This will trigger the accessor
            return $course;
        });

    return response()->json($courses);
});

// Student Dashboard - Get courses enrolled by student
Route::get('current-courses/{id}',function ($id){
    $courses = \App\Models\Course::with(['media','enrollments','category','instructor','exams'])
        ->whereHas('enrollments', function ($query) use ($id) {
            $query->where('user_id', $id);
        })
        ->get()
        ->map(function($course) {
            // Add cover_image information using the accessor
            $course->cover_image; // This will trigger the accessor
            return $course;
        });

    return response()->json($courses);
});

// Student Courses - Alternative endpoint
Route::get('student-courses/{id}', function ($id) {
    $enrollments = \App\Models\Enrollment::with(['course.media','course.category','course.instructor','course.exams'])
        ->where('user_id', $id)
        ->get();

    $courses = $enrollments->map(function($enrollment) {
        $course = $enrollment->course;
        $course->enrollment_progress = $enrollment->progress;
        $course->enrollment_status = $enrollment->status;
        $course->enrolled_at = $enrollment->enrolled_at;
        
        // Add cover_image information using the accessor
        $course->cover_image; // This will trigger the accessor
        return $course;
    });

    return response()->json($courses);
});

Route::post('enrolement/progress/update',function (Request $request){
    $enrollment = \App\Models\Enrollment::findOrFail($request->enrollmentId);
    $enrollment->progress = $request->progress;
    $enrollment->save();

    return response()->json(['success' => true]);
});

Route::get('dashboard-user/{id}',function ($id){
    $user = \App\Models\User::with(['enrollments.course'])->find($id);
    // Nombre de cours terminés (progress = 100)
    $completedCoursesCount = $user->enrollments()->where('progress', 100)->count();
    // Nombre de certificats obtenus (basé sur les cours terminés pour l'instant)
    $certificatesDownloadedCount = $completedCoursesCount; // Utiliser les cours terminés comme proxy pour les certificats

    // Total price of all enrollments - use course price if price_paid is 0
    $totalCoursePrice = 0;
    foreach ($user->enrollments as $enrollment) {
        $price = $enrollment->price_paid > 0 ? $enrollment->price_paid : ($enrollment->course->price ?? 0);
        $totalCoursePrice += $price;
    }
    // Round to 2 decimal places to avoid floating point precision issues
    $totalCoursePrice = round($totalCoursePrice, 2);

    $categoryStats = \App\Models\Enrollment::where('user_id', $user->id)
        ->with('course.category')
        ->get()
        ->groupBy(fn($enroll) => $enroll->course && $enroll->course->category ? $enroll->course->category->name : 'Sans catégorie')
        ->map(function ($group) {
            $totalSpent = 0;
            foreach ($group as $enrollment) {
                $price = $enrollment->price_paid > 0 ? $enrollment->price_paid : ($enrollment->course->price ?? 0);
                $totalSpent += $price;
            }
            return [
                'count' => $group->count(),
                'total_spent' => $totalSpent,
            ];
        });

    $enrollments = \App\Models\Enrollment::with(['course.media','course.category','course.instructor'])
        ->where('user_id', $user->id)
        ->get()
        ->map(function($enrollment) {
            // Add cover_image information to course using the accessor
            if ($enrollment->course) {
                $enrollment->course->cover_image; // This will trigger the accessor
            }
            return $enrollment;
        });

    $enrollmentsPerCategory = \App\Models\Enrollment::with('course.category')
        ->where('user_id', $user->id)
        ->get()
        ->groupBy(fn($enrollment) => $enrollment->course && $enrollment->course->category ? $enrollment->course->category->name : 'Sans catégorie')
        ->map(fn($group) => $group->count());

    $data = DB::table('enrollments')
        ->join('courses', 'enrollments.course_id', '=', 'courses.id')
        ->join('categories', 'courses.category_id', '=', 'categories.id')
        ->select('categories.name as category', DB::raw('count(*) as total'))
        ->groupBy('categories.name')
        ->get();

    return response()->json(['courses' => $user->enrollments,
        'completedCoursesCount'=>$completedCoursesCount,
        'totalCoursePrice' => $totalCoursePrice,
        'categoryStat' => $categoryStats,
        'enrollments' => $enrollments,
        'data' => $data,
        'enrollmentsPerCategory' => $enrollmentsPerCategory,
        'certificatesDownloadedCount'=>$certificatesDownloadedCount]);
});

Route::get('rollements-course/{id}/{idUser}', function ($id,$idUser){
   $course  =  \App\Models\Enrollment::where('user_id',$idUser)->where('course_id',$id)->first();

   return response()->json($course);
});

// Settings & Options
Route::get('settings', 'App\Http\Controllers\SettingController@frontSettings');

// File serving routes
Route::get('files/course-covers/{filename}', function ($filename) {
    // Find the attachment by filename
    $attachment = \App\Models\Attachment::where('file_name', $filename)
        ->orWhere('file_name', 'like', '%' . $filename)
        ->first();
    
    if (!$attachment) {
        // Return a default course image if not found
        return redirect('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop&crop=center');
    }
    
    // If we have an original_url (external image), redirect to it
    if ($attachment->original_url) {
        return redirect($attachment->original_url);
    }
    
    // Check if file exists in storage
    $filePath = 'public/' . $attachment->src;
    if (!\Storage::exists($filePath)) {
        // Try alternative path
        $filePath = $attachment->src;
        if (!\Storage::exists($filePath)) {
            // Return default image if file doesn't exist
            return redirect('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop&crop=center');
        }
    }
    
    // Serve the file
    return response()->file(storage_path('app/' . $filePath));
});

// Authentication
Route::post('/login', 'App\Http\Controllers\AuthController@login')->name('login');
Route::post('/register', 'App\Http\Controllers\AuthController@register');
Route::post('/forgot-password', 'App\Http\Controllers\AuthController@forgotPassword');
Route::post('/verify-token', 'App\Http\Controllers\AuthController@verifyToken');
Route::post('/update-password', 'App\Http\Controllers\AuthController@updatePassword');

// Courses - Public Routes
Route::apiResource('course', 'App\Http\Controllers\CourseController',[
  'only' => ['index', 'show'],
]);

// Exams
Route::apiResource('examen', 'App\Http\Controllers\ExamController', [
  'only' => ['index', 'show'],
]);
Route::get('exams/course/{id}', 'App\Http\Controllers\ExamController@getExamByCourseId');

// Categories
Route::apiResource('category', 'App\Http\Controllers\CategoryController',[
  'only' => ['index', 'show'],
]);

// Quizzes
Route::apiResource('quiz', 'App\Http\Controllers\QuizController', [
  'only' => ['index', 'show'],
]);
Route::get('quizzes/course/{id}', 'App\Http\Controllers\QuizController@getQuizByCourseId');

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    // Authentication
  Route::post('logout', 'App\Http\Controllers\AuthController@logout');

  // Account
  Route::get('self', 'App\Http\Controllers\AccountController@self');
  Route::put('updateProfile', 'App\Http\Controllers\AccountController@updateProfile');
  Route::put('updatePassword', 'App\Http\Controllers\AccountController@updatePassword');

  // Quiz Sessions
  Route::post('quiz/start', 'App\Http\Controllers\API\QuizSessionController@start');
  Route::post('quiz/session/{id}/submit', 'App\Http\Controllers\API\QuizSessionController@submit');
  Route::get('quiz/session/{id}/results', 'App\Http\Controllers\API\QuizSessionController@results');
  Route::get('quiz/sessions', 'App\Http\Controllers\API\QuizSessionController@userSessions');
  Route::get('quiz/{id}/sessions', 'App\Http\Controllers\API\QuizSessionController@quizSessions');
  
  // Exam Sessions
  Route::post('exam/start', 'App\Http\Controllers\API\ExamSessionController@start');
  Route::post('exam/session/{id}/submit', 'App\Http\Controllers\API\ExamSessionController@submit');
  Route::get('exam/session/{id}/results', 'App\Http\Controllers\API\ExamSessionController@results');
  Route::get('exam/sessions', 'App\Http\Controllers\API\ExamSessionController@userSessions');
  Route::get('exam/{id}/sessions', 'App\Http\Controllers\API\ExamSessionController@examSessions');
  
  // Teacher Courses - Get courses taught by authenticated teacher
  Route::get('teacher-courses', function (Request $request) {
      $user = $request->user();
      
      $courses = \App\Models\Course::with(['media','enrollments.user','category','instructor','exams','quizzes'])
          ->where('user_id', $user->id)
          ->get()
          ->map(function($course) {
              // Add cover_image information using the accessor
              $course->cover_image; // This will trigger the accessor
              
              // Add enrollment statistics
              $course->total_enrollments = $course->enrollments->count();
              $course->completed_enrollments = $course->enrollments->where('progress', '>=', 100)->count();
              $course->active_enrollments = $course->enrollments->where('status', 'active')->count();
              
              // Add quiz/exam counts
              $course->total_quizzes = $course->quizzes->count();
              $course->total_exams = $course->exams->count();
              
              return $course;
          });

      return response()->json([
          'success' => true,
          'data' => $courses,
          'total' => $courses->count()
      ]);
  });
  
    // Teacher Course Creation - Simple endpoint
  Route::post('teacher/course/create-simple', function (Request $request) {
      try {
          \Log::info('Course creation started', ['request_data' => $request->all()]);
          
          $user = $request->user();
          \Log::info('User authenticated', ['user_id' => $user ? $user->id : null]);
          
          if (!$user) {
              return response()->json([
                  'success' => false,
                  'message' => 'User not authenticated'
              ], 401);
          }
          
          // Check if user has teacher role
          $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                           $user->roles()->where('name', 'TEACHER')->exists();
          
          \Log::info('Teacher role check', ['has_teacher_role' => $hasTeacherRole, 'user_roles' => $user->roles->pluck('name')]);
          
          if (!$hasTeacherRole) {
              return response()->json([
                  'success' => false,
                  'message' => 'Unauthorized: Teacher role required',
                  'user_roles' => $user->roles->pluck('name')
              ], 403);
          }
          
          \Log::info('Starting validation');
          
          // Validate request data
          $validatedData = $request->validate([
              'title' => 'required|string|max:255',
              'description' => 'nullable|string',
              'price' => 'nullable|numeric|min:0',
              'category_id' => 'nullable|exists:categories,id',
              'level' => 'nullable|string|in:beginner,intermediate,advanced',
              'language' => 'nullable|string|max:10',
              'requirements' => 'nullable|string',
              'what_you_will_learn' => 'nullable|string',
              'is_featured' => 'nullable|boolean',
              'is_published' => 'nullable|boolean',
              'status' => 'nullable|string|in:draft,published,archived',
              'max_students' => 'nullable|integer|min:1',
              'cover_image' => 'nullable|string',
              'duration' => 'nullable|integer|min:1'
          ]);
          
          \Log::info('Validation passed', ['validated_data' => $validatedData]);
          
          // Create course
          \Log::info('Creating course object');
          $course = new \App\Models\Course();
          $course->title = $validatedData['title'];
          $course->slug = \Str::slug($validatedData['title']) . '-' . time(); // Generate unique slug
          $course->description = $validatedData['description'] ?? null;
          $course->price = $validatedData['price'] ?? 0;
          $course->category_id = $validatedData['category_id'] ?? null;
          $course->level = $validatedData['level'] ?? 'beginner';
          $course->language = $validatedData['language'] ?? 'fr';
          $course->requirements = $validatedData['requirements'] ?? null;
          $course->what_you_will_learn = $validatedData['what_you_will_learn'] ?? null;
          $course->is_featured = $validatedData['is_featured'] ?? false;
          $course->is_published = $validatedData['is_published'] ?? false;
          $course->status = $validatedData['status'] ?? 'draft';
          $course->max_students = $validatedData['max_students'] ?? null;
          $course->duration = $validatedData['duration'] ?? 0; // Default to 0 instead of null
          $course->user_id = $user->id; // Set the teacher as course owner
          
          \Log::info('Saving course to database');
          $course->save();
          \Log::info('Course saved successfully', ['course_id' => $course->id]);
          
          // Handle cover image if provided
          if (!empty($validatedData['cover_image'])) {
              \Log::info('Creating cover image attachment');
              // Create media attachment for cover image
              $attachment = new \App\Models\Attachment();
              $attachment->attachable_type = 'App\Models\Course';
              $attachment->attachable_id = $course->id;
              $attachment->file_name = 'cover_' . $course->id . '.jpg';
              $attachment->src = $validatedData['cover_image'];
              $attachment->original_url = $validatedData['cover_image'];
              $attachment->save();
              \Log::info('Cover image attachment created');
          }
          
          // Load the course with relationships
          \Log::info('Loading course relationships');
          $course->load(['media', 'category', 'instructor']);
          $course->cover_image; // Trigger accessor
          
          \Log::info('Course creation completed successfully');
          
          return response()->json([
              'success' => true,
              'message' => 'Course created successfully',
              'data' => $course
          ], 201);
          
      } catch (\Illuminate\Validation\ValidationException $e) {
          \Log::error('Validation error in course creation', ['errors' => $e->errors()]);
          return response()->json([
              'success' => false,
              'message' => 'Validation failed',
              'errors' => $e->errors()
          ], 422);
      } catch (\Exception $e) {
          \Log::error('Course creation error: ' . $e->getMessage(), [
              'exception' => $e,
              'trace' => $e->getTraceAsString(),
              'request_data' => $request->all()
          ]);
          return response()->json([
              'success' => false,
              'message' => 'Failed to create course: ' . $e->getMessage(),
              'error_details' => [
                  'file' => $e->getFile(),
                  'line' => $e->getLine(),
                  'trace' => $e->getTraceAsString()
              ]
          ], 500);
      }
  });
   
   // Test Angular-Laravel connection endpoint
   Route::post('test/angular-connection', function (Request $request) {
       $user = $request->user();
       
       // Check if user has teacher role
       $hasTeacherRole = false;
       if ($user) {
           $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                            $user->roles()->where('name', 'TEACHER')->exists();
       }
       
       return response()->json([
           'success' => true,
           'message' => 'Angular-Laravel connection successful',
           'timestamp' => now()->toISOString(),
           'authenticated' => $user ? true : false,
           'user_id' => $user ? $user->id : null,
           'user_name' => $user ? $user->name : null,
           'user_email' => $user ? $user->email : null,
           'has_teacher_role' => $hasTeacherRole,
           'request_data' => $request->all()
       ]);
   });
   
   // Teacher Chapter Management Routes
   Route::post('teacher/course/{courseId}/chapter', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           if (!$user) {
               return response()->json([
                   'success' => false,
                   'message' => 'User not authenticated'
               ], 401);
           }
           
           // Check if user has teacher role
           $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                            $user->roles()->where('name', 'TEACHER')->exists();
           
           if (!$hasTeacherRole) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: Teacher role required'
               ], 403);
           }
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only manage your own courses'
               ], 403);
           }
           
           // Validate chapter data
           $validatedData = $request->validate([
               'title' => 'required|string|max:255',
               'description' => 'nullable|string',
               'order' => 'nullable|integer|min:1',
               'is_published' => 'nullable|boolean',
               'is_free' => 'nullable|boolean',
               'contents' => 'nullable|array',
               'contents.*.title' => 'required_with:contents|string|max:255',
               'contents.*.type' => 'required_with:contents|string|in:video,text,pdf,audio',
               'contents.*.url' => 'nullable|url',
               'contents.*.duration' => 'nullable|integer|min:0',
               'contents.*.is_free' => 'nullable|boolean'
           ]);
           
           // Create chapter
           $chapter = new \App\Models\Chapter();
           $chapter->title = $validatedData['title'];
           $chapter->description = $validatedData['description'] ?? null;
           $chapter->course_id = $courseId;
           $chapter->order = $validatedData['order'] ?? 1;
           $chapter->is_published = $validatedData['is_published'] ?? true;
           $chapter->is_free = $validatedData['is_free'] ?? false;
           $chapter->save();
           
           // Create contents if provided
           if (!empty($validatedData['contents'])) {
               foreach ($validatedData['contents'] as $index => $contentData) {
                   $content = new \App\Models\Content();
                   $content->title = $contentData['title'];
                   $content->type = $contentData['type'];
                   $content->duration = $contentData['duration'] ?? 0;
                   $content->serial_number = $index + 1; // Use index for ordering
                   $content->is_forwardable = false;
                   $content->is_free = $contentData['is_free'] ?? false;
                   $content->media_link = $contentData['url'] ?? null;
                   $content->chapter_id = $chapter->id;
                   $content->save();
               }
           }
           
           // Load chapter with contents
           $chapter->load('contents');
           
           return response()->json([
               'success' => true,
               'message' => 'Chapter created successfully',
               'data' => $chapter
           ], 201);
           
       } catch (\Illuminate\Validation\ValidationException $e) {
           return response()->json([
               'success' => false,
               'message' => 'Validation failed',
               'errors' => $e->errors()
           ], 422);
       } catch (\Exception $e) {
           \Log::error('Chapter creation error: ' . $e->getMessage());
           return response()->json([
               'success' => false,
               'message' => 'Failed to create chapter: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Get course chapters
   Route::get('teacher/course/{courseId}/chapters', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only view your own courses'
               ], 403);
           }
           
           $chapters = \App\Models\Chapter::with('contents')
               ->where('course_id', $courseId)
               ->orderBy('order')
               ->get();
           
           return response()->json([
               'success' => true,
               'data' => $chapters
           ]);
           
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Failed to load chapters: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Get course quizzes
   Route::get('teacher/course/{courseId}/quizzes', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only view your own courses'
               ], 403);
           }
           
           $quizzes = \App\Models\Quiz::with('questions')
               ->where('course_id', $courseId)
               ->orderBy('created_at', 'desc')
               ->get();
           
           return response()->json([
               'success' => true,
               'quizzes' => $quizzes
           ]);
           
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Failed to load quizzes: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Teacher Quiz Management Routes
   Route::post('teacher/course/{courseId}/quiz', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           if (!$user) {
               return response()->json([
                   'success' => false,
                   'message' => 'User not authenticated'
               ], 401);
           }
           
           // Check if user has teacher role
           $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                            $user->roles()->where('name', 'TEACHER')->exists();
           
           if (!$hasTeacherRole) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: Teacher role required'
               ], 403);
           }
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only manage your own courses'
               ], 403);
           }
           
           // Validate quiz data
           $validatedData = $request->validate([
               'title' => 'required|string|max:255',
               'description' => 'nullable|string',
               'duration' => 'nullable|integer|min:1',
               'total_marks' => 'nullable|integer|min:1',
               'passing_marks' => 'nullable|integer|min:0',
               'max_attempts' => 'nullable|integer|min:1',
               'is_published' => 'nullable|boolean',
               'shuffle_questions' => 'nullable|boolean',
               'questions' => 'nullable|array',
               'questions.*.question' => 'required_with:questions|string',
               'questions.*.type' => 'required_with:questions|string|in:multiple_choice,single_choice,binary',
               'questions.*.options' => 'nullable|array',
               'questions.*.correct_answer' => 'required_with:questions|string',
               'questions.*.marks' => 'nullable|integer|min:0'
           ]);
           
           // Create quiz
           $quiz = new \App\Models\Quiz();
           $quiz->title = $validatedData['title'];
           $quiz->description = $validatedData['description'] ?? null;
           $quiz->course_id = $courseId;
           $quiz->duration = $validatedData['duration'] ?? 30;
           $quiz->total_marks = $validatedData['total_marks'] ?? 50;
           $quiz->passing_marks = $validatedData['passing_marks'] ?? 30;
           $quiz->max_attempts = $validatedData['max_attempts'] ?? 3;
           $quiz->is_published = $validatedData['is_published'] ?? false;
           $quiz->shuffle_questions = $validatedData['shuffle_questions'] ?? false;
           $quiz->save();
           
           // Create questions if provided
           if (!empty($validatedData['questions'])) {
               foreach ($validatedData['questions'] as $index => $questionData) {
                   $question = new \App\Models\Question();
                   $question->question = $questionData['question'];
                   $question->type = $questionData['type'];
                   $question->options = json_encode($questionData['options'] ?? []);
                   $question->correct_answer = $questionData['correct_answer'];
                   $question->marks = $questionData['marks'] ?? 1;
                   $question->order = $index + 1;
                   $question->course_id = $courseId;
                   $question->quiz_id = $quiz->id;
                   $question->is_active = true;
                   $question->save();
               }
           }
           
           // Load quiz with questions
           $quiz->load('questions');
           
           return response()->json([
               'success' => true,
               'message' => 'Quiz created successfully',
               'data' => $quiz
           ], 201);
           
       } catch (\Illuminate\Validation\ValidationException $e) {
           return response()->json([
               'success' => false,
               'message' => 'Validation failed',
               'errors' => $e->errors()
           ], 422);
       } catch (\Exception $e) {
           \Log::error('Quiz creation error: ' . $e->getMessage());
           return response()->json([
               'success' => false,
               'message' => 'Failed to create quiz: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Get course exams
   Route::get('teacher/course/{courseId}/exams', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only view your own courses'
               ], 403);
           }
           
           $exams = \App\Models\Exam::with('questions')
               ->where('course_id', $courseId)
               ->orderBy('created_at', 'desc')
               ->get();
           
           return response()->json([
               'success' => true,
               'exams' => $exams
           ]);
           
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Failed to load exams: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Teacher Exam Management Routes
   Route::post('teacher/course/{courseId}/exam', function (Request $request, $courseId) {
       try {
           $user = $request->user();
           
           if (!$user) {
               return response()->json([
                   'success' => false,
                   'message' => 'User not authenticated'
               ], 401);
           }
           
           // Check if user has teacher role
           $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                            $user->roles()->where('name', 'TEACHER')->exists();
           
           if (!$hasTeacherRole) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: Teacher role required'
               ], 403);
           }
           
           // Verify course ownership
           $course = \App\Models\Course::find($courseId);
           if (!$course || $course->user_id !== $user->id) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: You can only manage your own courses'
               ], 403);
           }
           
           // Validate exam data
           $validatedData = $request->validate([
               'title' => 'required|string|max:255',
               'description' => 'nullable|string',
               'duration' => 'nullable|integer|min:1',
               'total_marks' => 'nullable|integer|min:1',
               'passing_marks' => 'nullable|integer|min:0',
               'max_attempts' => 'nullable|integer|min:1',
               'is_published' => 'nullable|boolean',
               'shuffle_questions' => 'nullable|boolean',
               'questions' => 'nullable|array',
               'questions.*.question' => 'required_with:questions|string',
               'questions.*.type' => 'required_with:questions|string|in:multiple_choice,single_choice,binary',
               'questions.*.options' => 'nullable|array',
               'questions.*.correct_answer' => 'required_with:questions|string',
               'questions.*.marks' => 'nullable|integer|min:0'
           ]);
           
           // Create exam
           $exam = new \App\Models\Exam();
           $exam->title = $validatedData['title'];
           $exam->description = $validatedData['description'] ?? null;
           $exam->course_id = $courseId;
           $exam->duration = $validatedData['duration'] ?? 60;
           $exam->total_marks = $validatedData['total_marks'] ?? 100;
           $exam->passing_marks = $validatedData['passing_marks'] ?? 60;
           $exam->max_attempts = $validatedData['max_attempts'] ?? 1;
           $exam->is_published = $validatedData['is_published'] ?? false;
           $exam->shuffle_questions = $validatedData['shuffle_questions'] ?? false;
           $exam->total_questions = 0; // Will be updated after questions are created
           $exam->save();
           
           // Create questions if provided
           if (!empty($validatedData['questions'])) {
               foreach ($validatedData['questions'] as $index => $questionData) {
                   $question = new \App\Models\Question();
                   $question->question = $questionData['question'];
                   $question->type = $questionData['type'];
                   $question->options = json_encode($questionData['options'] ?? []);
                   $question->correct_answer = $questionData['correct_answer'];
                   $question->marks = $questionData['marks'] ?? 5;
                   $question->order = $index + 1;
                   $question->course_id = $courseId;
                   $question->exam_id = $exam->id;
                   $question->is_active = true;
                   $question->save();
               }
               
               // Update total_questions count
               $exam->total_questions = count($validatedData['questions']);
               $exam->save();
           }
           
           // Load exam with questions
           $exam->load('questions');
           
                       return response()->json([
                'success' => true,
                'message' => 'Exam created successfully',
                'data' => $exam
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Exam creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Additional Teacher Management Routes
    
    // Update Chapter
    Route::put('teacher/chapter/{chapterId}', function (Request $request, $chapterId) {
        try {
            $user = $request->user();
            $chapter = \App\Models\Chapter::find($chapterId);
            
            if (!$chapter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chapter not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($chapter->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'nullable|integer|min:1',
                'is_published' => 'nullable|boolean',
                'is_free' => 'nullable|boolean'
            ]);
            
                         $chapter->update([
                 'title' => $validatedData['title'],
                 'description' => $validatedData['description'] ?? $chapter->description,
                 'order' => $validatedData['order'] ?? $chapter->order,
                 'is_published' => $validatedData['is_published'] ?? $chapter->is_published,
                 'is_free' => $validatedData['is_free'] ?? $chapter->is_free
             ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Chapter updated successfully',
                'data' => $chapter
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update chapter: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Delete Chapter
    Route::delete('teacher/chapter/{chapterId}', function (Request $request, $chapterId) {
        try {
            $user = $request->user();
            $chapter = \App\Models\Chapter::find($chapterId);
            
            if (!$chapter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chapter not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($chapter->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $chapter->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Chapter deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete chapter: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Update Quiz
    Route::put('teacher/quiz/{quizId}', function (Request $request, $quizId) {
        try {
            $user = $request->user();
            $quiz = \App\Models\Quiz::find($quizId);
            
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($quiz->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'time_limit' => 'nullable|integer|min:1',
                'max_attempts' => 'nullable|integer|min:1',
                'passing_score' => 'nullable|numeric|min:0|max:100',
                'is_published' => 'nullable|boolean'
            ]);
            
            $quiz->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz updated successfully',
                'data' => $quiz
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quiz: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Delete Quiz
    Route::delete('teacher/quiz/{quizId}', function (Request $request, $quizId) {
        try {
            $user = $request->user();
            $quiz = \App\Models\Quiz::find($quizId);
            
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($quiz->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $quiz->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Update Exam
    Route::put('teacher/exam/{examId}', function (Request $request, $examId) {
        try {
            $user = $request->user();
            $exam = \App\Models\Exam::find($examId);
            
            if (!$exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($exam->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'time_limit' => 'nullable|integer|min:1',
                'max_attempts' => 'nullable|integer|min:1',
                'passing_score' => 'nullable|numeric|min:0|max:100',
                'is_published' => 'nullable|boolean'
            ]);
            
            $exam->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully',
                'data' => $exam
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Delete Exam
    Route::delete('teacher/exam/{examId}', function (Request $request, $examId) {
        try {
            $user = $request->user();
            $exam = \App\Models\Exam::find($examId);
            
            if (!$exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam not found'
                ], 404);
            }
            
            // Verify course ownership
            $course = \App\Models\Course::find($exam->course_id);
            if (!$course || $course->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only manage your own courses'
                ], 403);
            }
            
            $exam->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam: ' . $e->getMessage()
            ], 500);
        }
    });
   
   // Course cover upload endpoint
   Route::post('course/upload-cover-simple', function (Request $request) {
       try {
           $user = $request->user();
           
           if (!$user) {
               return response()->json([
                   'success' => false,
                   'message' => 'User not authenticated'
               ], 401);
           }
           
           // Check if user has teacher role
           $hasTeacherRole = $user->roles()->where('name', 'teacher')->exists() || 
                            $user->roles()->where('name', 'TEACHER')->exists();
           
           if (!$hasTeacherRole) {
               return response()->json([
                   'success' => false,
                   'message' => 'Unauthorized: Teacher role required'
               ], 403);
           }
           
           // Validate file upload
           $request->validate([
               'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
           ]);
           
           $file = $request->file('cover_image');
           
           // Generate unique filename
           $filename = 'course_cover_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
           
           // Store file in public/course-covers directory
           $path = $file->storeAs('course-covers', $filename, 'public');
           
           // Generate full URL for the uploaded file
           $baseUrl = request()->getSchemeAndHttpHost();
           $fullUrl = $baseUrl . '/admin/api/files/course-covers/' . $filename;
           
           return response()->json([
               'success' => true,
               'message' => 'Cover image uploaded successfully',
               'cover_image_path' => '/storage/' . $path,
               'file_name' => $filename,
               'full_url' => $fullUrl,
               'proxy_url' => $fullUrl
           ]);
           
       } catch (\Illuminate\Validation\ValidationException $e) {
           return response()->json([
               'success' => false,
               'message' => 'Validation failed',
               'errors' => $e->errors()
           ], 422);
       } catch (\Exception $e) {
           \Log::error('Course cover upload error: ' . $e->getMessage());
           return response()->json([
               'success' => false,
               'message' => 'Failed to upload cover image: ' . $e->getMessage()
           ], 500);
       }
   });
   
   // Debug course creation endpoint
   Route::post('debug/course-creation', function (Request $request) {
       try {
           $user = $request->user();
           
           return response()->json([
               'success' => true,
               'message' => 'Debug endpoint reached',
               'user' => $user ? [
                   'id' => $user->id,
                   'name' => $user->name,
                   'email' => $user->email,
                   'roles' => $user->roles->pluck('name')
               ] : null,
               'request_data' => $request->all(),
               'timestamp' => now()->toISOString()
           ]);
           
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Debug error: ' . $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ], 500);
       }
   });
  
  // Validate Certification
  Route::post('validate-certification', function (Request $request) {
      try {
          $request->validate([
              'enrollment_id' => 'required|exists:enrollments,id',
              'student_id' => 'required|exists:users,id',
              'course_id' => 'required|exists:courses,id',
              'average_score' => 'required|numeric|min:0|max:100',
              'quizzes_completed' => 'required|integer|min:0',
              'total_quizzes' => 'required|integer|min:0'
          ]);

          $enrollment = \App\Models\Enrollment::find($request->enrollment_id);
          
          // Verify that the authenticated user is the course instructor
          $course = \App\Models\Course::find($request->course_id);
          $user = $request->user();
          
          if ($course->user_id !== $user->id) {
              return response()->json([
                  'success' => false,
                  'message' => 'Unauthorized: You are not the instructor of this course'
              ], 403);
          }
          
          // Update enrollment with certification data
          $enrollment->update([
              'is_certified' => true,
              'certification_date' => now(),
              'average_score' => $request->average_score,
              'quizzes_completed' => $request->quizzes_completed,
              'total_quizzes' => $request->total_quizzes,
              'certified_by' => $user->id,
              'congratulations_shown' => false // New field to track if congratulations was shown
          ]);

          return response()->json([
              'success' => true,
              'message' => 'Certification validated successfully',
              'enrollment' => $enrollment
          ]);

      } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json([
              'success' => false,
              'message' => 'Validation failed',
              'errors' => $e->errors()
          ], 422);
      } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'message' => 'Failed to validate certification: ' . $e->getMessage()
          ], 500);
      }
  });
  
  // Check for pending congratulations
  Route::get('check-congratulations', function (Request $request) {
      $user = $request->user();
      
      // Find enrollments that are certified but congratulations haven't been shown
      $pendingCongratulations = \App\Models\Enrollment::with(['course.media', 'course.category'])
          ->where('user_id', $user->id)
          ->where('is_certified', true)
          ->where('congratulations_shown', false)
          ->get()
          ->map(function($enrollment) {
              $course = $enrollment->course;
              if ($course) {
                  $course->cover_image; // Trigger accessor
              }
              return $enrollment;
          });

      return response()->json([
          'success' => true,
          'has_pending' => $pendingCongratulations->count() > 0,
          'congratulations' => $pendingCongratulations
      ]);
  });
  
  // Mark congratulations as shown
  Route::post('mark-congratulations-shown', function (Request $request) {
      $request->validate([
          'enrollment_id' => 'required|exists:enrollments,id'
      ]);
      
      $user = $request->user();
      $enrollment = \App\Models\Enrollment::where('id', $request->enrollment_id)
          ->where('user_id', $user->id)
          ->first();
          
      if (!$enrollment) {
          return response()->json([
              'success' => false,
              'message' => 'Enrollment not found or unauthorized'
          ], 404);
      }
      
      $enrollment->update(['congratulations_shown' => true]);
      
      return response()->json([
          'success' => true,
          'message' => 'Congratulations marked as shown'
      ]);
  });
  
  // Submit course feedback
  Route::post('submit-course-feedback', function (Request $request) {
      $request->validate([
          'enrollment_id' => 'required|exists:enrollments,id',
          'rating' => 'required|integer|min:1|max:5',
          'feedback_text' => 'nullable|string|max:1000',
          'reaction' => 'nullable|string|in:excellent,useful,engaging,recommend'
      ]);
      
      $user = $request->user();
      $enrollment = \App\Models\Enrollment::where('id', $request->enrollment_id)
          ->where('user_id', $user->id)
          ->first();
          
      if (!$enrollment) {
          return response()->json([
              'success' => false,
              'message' => 'Enrollment not found or unauthorized'
          ], 404);
      }
      
      // Update enrollment with feedback
      $enrollment->update([
          'feedback_rating' => $request->rating,
          'feedback_text' => $request->feedback_text,
          'feedback_reaction' => $request->reaction,
          'feedback_submitted_at' => now()
      ]);
      
      // Update course rating
      $course = $enrollment->course;
      if ($course) {
          $avgRating = \App\Models\Enrollment::where('course_id', $course->id)
              ->whereNotNull('feedback_rating')
              ->avg('feedback_rating');
          $totalReviews = \App\Models\Enrollment::where('course_id', $course->id)
              ->whereNotNull('feedback_rating')
              ->count();
              
          $course->update([
              'rating' => round($avgRating, 2),
              'total_reviews' => $totalReviews
          ]);
      }
      
      return response()->json([
          'success' => true,
          'message' => 'Feedback submitted successfully'
      ]);
  });
});

// Test endpoint to check question format
Route::get('test-question-format', function () {
    $question = \App\Models\Question::first();
    if ($question) {
  return response()->json([
            'question' => $question,
            'raw_attributes' => $question->getAttributes(),
            'options_type' => gettype($question->options),
            'options_value' => $question->options,
            'correct_answer' => $question->correct_answer
        ]);
    }
    return response()->json(['message' => 'No questions found']);
});

// Debug route to check exam questions format
Route::get('debug-exam-questions/{examId}', function ($examId) {
    $exam = \App\Models\Exam::with('questions')->find($examId);
    if (!$exam) {
        return response()->json(['message' => 'Exam not found']);
    }
    
    $questionsDebug = $exam->questions->map(function ($question) {
        return [
            'id' => $question->id,
            'question' => $question->question,
            'type' => $question->type,
            'raw_options' => $question->getAttributes()['options'] ?? null,
            'model_options' => $question->options,
            'correct_answer' => $question->correct_answer,
            'options_type' => gettype($question->options),
        ];
    });
    
    return response()->json([
        'exam_id' => $exam->id,
        'exam_title' => $exam->title,
        'questions_debug' => $questionsDebug
    ]);
  });

// Save enrollment endpoint
Route::post('save-enrollment', function (Request $request) {
    try {
        // Validate required fields
      $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
            'course_price' => 'nullable|numeric|min:0',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string|in:active,completed,suspended'
        ]);

        // Check if enrollment already exists
        $existingEnrollment = \App\Models\Enrollment::where('course_id', $request->course_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingEnrollment) {
      return response()->json([
                'success' => false,
                'message' => 'User is already enrolled in this course',
                'enrollment' => $existingEnrollment,
                'identifier' => 'ENR-' . str_pad($existingEnrollment->id, 6, '0', STR_PAD_LEFT)
            ], 409);
        }

        // Create new enrollment
        $enrollment = new \App\Models\Enrollment();
        $enrollment->course_id = $request->course_id;
        $enrollment->user_id = $request->user_id;
        $enrollment->progress = $request->progress ?? 0;
        $enrollment->status = $request->status ?? 'active';
        $enrollment->enrolled_at = now();
        $enrollment->price_paid = $request->course_price ?? 0;
        $enrollment->save();

        // Generate identifier based on enrollment ID
        $identifier = 'ENR-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT);
      
      return response()->json([
        'success' => true,
            'message' => 'Enrollment created successfully',
            'enrollment' => $enrollment,
            'identifier' => $identifier
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
      return response()->json([
            'success' => false,
            'message' => 'Failed to create enrollment: ' . $e->getMessage()
        ], 500);
    }
});

// Get user enrollments
Route::middleware('auth:sanctum')->get('/enrollments', function (Request $request) {
    $user = $request->user();
    $enrollments = \App\Models\Enrollment::with(['course.media','course.category','course.instructor'])
        ->where('user_id', $user->id)
        ->get()
        ->map(function($enrollment) {
            // Add cover_image information to course using the accessor
            if ($enrollment->course) {
                $enrollment->course->cover_image; // This will trigger the accessor
            }
            return $enrollment;
        });

    return response()->json([
        'enrollments' => $enrollments,
        'total' => $enrollments->count()
    ]);
});

// Debug endpoint to check user enrollments with detailed status
Route::middleware('auth:sanctum')->get('/debug/enrollments', function (Request $request) {
      $user = $request->user();
    $enrollments = \App\Models\Enrollment::with(['course'])
        ->where('user_id', $user->id)
        ->get();

    $enrollmentDetails = $enrollments->map(function($enrollment) {
        return [
            'id' => $enrollment->id,
            'course_id' => $enrollment->course_id,
            'course_title' => $enrollment->course->title ?? 'Unknown',
            'status' => $enrollment->status,
            'progress' => $enrollment->progress,
            'enrolled_at' => $enrollment->enrolled_at,
            'price_paid' => $enrollment->price_paid,
            'created_at' => $enrollment->created_at,
            'updated_at' => $enrollment->updated_at
        ];
    });

    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'enrollments' => $enrollmentDetails,
        'total_enrollments' => $enrollments->count()
    ]);
});

// Gemini AI Chat endpoint
Route::middleware('auth:sanctum')->post('/gemini/chat', function (Request $request) {
    try {
      $request->validate([
            'message' => 'required|string|max:1000',
            'system_prompt' => 'nullable|string|max:500'
        ]);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key not configured',
                'fallback_response' => 'Je suis désolé, le service IA n\'est pas disponible pour le moment. Pouvez-vous reformuler votre question ?'
            ], 503);
        }

        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
        
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $request->system_prompt 
                                ? $request->system_prompt . "\n\nUser: " . $request->message
                                : $request->message
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($apiUrl . '?key=' . $apiKey, $requestBody);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['candidates']) && count($data['candidates']) > 0) {
      return response()->json([
        'success' => true,
                    'response' => $data['candidates'][0]['content']['parts'][0]['text']
                ]);
            } else {
                throw new Exception('No response from Gemini API');
            }
        } else {
            throw new Exception('Gemini API request failed: ' . $response->body());
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Gemini API Error: ' . $e->getMessage());
        
        // Provide fallback responses
        $fallbackResponses = [
            "Je comprends votre question. Pouvez-vous me donner plus de détails ?",
            "C'est une excellente question ! Laissez-moi vous aider avec cela.",
            "Basé sur votre message, je recommande de consulter les ressources de cours disponibles.",
            "Pour mieux vous aider, pourriez-vous préciser votre niveau d'expérience ?",
            "Je vais vous donner quelques suggestions personnalisées pour votre apprentissage."
        ];
        
        return response()->json([
            'success' => false,
            'message' => 'AI service temporarily unavailable',
            'fallback_response' => $fallbackResponses[array_rand($fallbackResponses)]
        ], 503);
    }
});

// Test endpoints for dashboard (without authentication for debugging)
Route::prefix('test')->group(function () {
    Route::get('/enrollments/statistics', function () {
        $totalEnrollments = \App\Models\Enrollment::count();
        $activeEnrollments = \App\Models\Enrollment::where('status', 'active')->count();
        $pendingEnrollments = \App\Models\Enrollment::where('status', 'pending')->count();
        $cancelledEnrollments = \App\Models\Enrollment::where('status', 'cancelled')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_enrollments' => $totalEnrollments,
                    'active_enrollments' => $activeEnrollments,
                    'pending_enrollments' => $pendingEnrollments,
                    'cancelled_enrollments' => $cancelledEnrollments,
                ]
            ]
        ]);
    });
    
    Route::get('/enrollments/revenue-summary', function () {
        $totalRevenue = \App\Models\Enrollment::where('status', 'active')->sum('amount_paid');
        $charityAmount = $totalRevenue * 0.03;
        $platformFee = $totalRevenue * 0.05;
        $instructorEarnings = $totalRevenue - $charityAmount - $platformFee;
        
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'charity_amount' => $charityAmount,
                    'platform_fee' => $platformFee,
                    'instructor_earnings' => $instructorEarnings,
                ]
            ]
        ]);
    });
    
    Route::get('/revenue/monthly-breakdown', function () {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyData = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthRevenue = rand(1000, 5000);
            $monthlyData[] = [
                'month' => $months[$i - 1],
                'month_number' => $i,
                'total_revenue' => $monthRevenue,
                'charity_amount' => $monthRevenue * 0.03,
                'platform_fee' => $monthRevenue * 0.05,
                'instructor_earnings' => $monthRevenue * 0.92,
                'transactions' => rand(10, 50),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'year' => date('Y'),
                'monthly_data' => $monthlyData,
            ]
        ]);
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Badge endpoint (for dashboard menu)
    Route::get('/badge', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_notifications' => 0,
                'pending_orders' => 0,
                'new_messages' => 0,
            ]
        ]);
    });
    
    // Notifications endpoint
    Route::get('/notifications', function () {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    });
    
    // Admin enrollment management
    Route::prefix('enrollments')->group(function () {
        Route::get('/', [EnrollmentController::class, 'adminIndex']);
        Route::get('/statistics', [EnrollmentController::class, 'adminStatistics']);
        Route::get('/revenue-summary', [EnrollmentController::class, 'revenueSummary']);
        Route::get('/{id}', [EnrollmentController::class, 'adminShow']);
        Route::put('/{id}/status', [EnrollmentController::class, 'updateStatus']);
    });
    
    // Admin revenue tracking
    Route::prefix('revenue')->group(function () {
        Route::get('/summary', [RevenueController::class, 'adminSummary']);
        Route::get('/charity-contributions', [RevenueController::class, 'charityContributions']);
        Route::get('/monthly-breakdown', [RevenueController::class, 'monthlyBreakdown']);
    });
}); 