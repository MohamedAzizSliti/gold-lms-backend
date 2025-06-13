<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Http\Controllers\API\EnrollmentController;

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
Route::get('current-courses/{id}',function ($id){
    $courses = \App\Models\Course::with(['media','enrollments','category','instructor','exams'])
        ->whereHas('enrollments', function ($query) use ($id) {
            $query->where('user_id', $id);
        })
        ->get();

    return response()->json($courses);
});
Route::post('enrolement/progress/update',function (Request $request){
    $enrollment = \App\Models\Enrollment::findOrFail($request->enrollmentId);
    $enrollment->progress = $request->progress;
    $enrollment->save();

    return response()->json(['success' => true]);
});
Route::get('dashboard-user/{id}',function ($id){
    $user = \App\Models\User::with(['enrollments'])->find($id);
    // Nombre de cours terminés (progress = 100)
    $completedCoursesCount = $user->enrollments()->where('progress', 100)->count();
    // Nombre de certificats obtenus (is_certaficate_downloaded = true)
    $certificatesDownloadedCount = $user->enrollments()->where('is_certificate_downloaded', true)->count();

    // Total price of all enrollments
    $totalCoursePrice = $user->enrollments()->sum('course_price');

    $categoryStats = \App\Models\Enrollment::where('user_id', $user->id)
        ->with('course.category')
        ->get()
        ->groupBy(fn($enroll) => $enroll->course->category->name)
        ->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_spent' => $group->sum('course_price'),
            ];
        });

    $enrollments = \App\Models\Enrollment::with(['course.media','course.category','course.instructor'])
        ->where('user_id', $user->id)
        ->get();

    $enrollmentsPerCategory = \App\Models\Enrollment::with('course.category')
        ->get()
        ->groupBy(fn($enrollment) => $enrollment->course->category->name ?? 'Sans catégorie')
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

// Countries & States
Route::apiResource('state', 'App\Http\Controllers\StateController');
Route::apiResource('chapter', 'App\Http\Controllers\ChapterController');
Route::apiResource('country', 'App\Http\Controllers\CountryController');

// Settings & Options
Route::get('settings', 'App\Http\Controllers\SettingController@frontSettings');
Route::get('rollements-course/{id}/{idUser}', function ($id,$idUser){
   $course  =  \App\Models\Enrollment::where('user_id',$idUser)->where('course_id',$id)->first();

   return response()->json($course);
});

Route::get('settings-app-mobile', 'App\Http\Controllers\SettingController@appMobileSettings');
Route::get('themeOptions', 'App\Http\Controllers\ThemeOptionController@index');
Route::post('save-enrollment', function (Request $request) {
    $enrolement =  \App\Models\Enrollment::create($request->all());
    $course = \App\Models\Course::find($request->input('course_id'))->first();
    $user = \App\Models\User::find($request->input('user_id'))->first();
    $transaction = new \App\Models\Transaction();
    $transaction->course_title = $course->title;
    $transaction->user_phone = $user->phone ;
    $transaction->payment_method = 'stripe' ;
    $transaction->is_paid = true;
    $transaction->paid_at = Carbon::now() ;
    $transaction->payment_amount = $request->input('course_price');
    $transaction->enrollment_id = $enrolement->id ;
    $transaction->course_id = $course->id ;
    $transaction->user_id = $request->input('user_id') ;
    $transaction->identifier =  strtoupper(Str::random(10)); ;
    $transaction->save();


    return response()->json($transaction);

}) ;




// Webhooks
Route::post('/paypal/webhook', 'App\Http\Controllers\WebhookController@paypal')->name('paypal.webhook');


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
Route::get('course/slug/{slug}', 'App\Http\Controllers\ProductController@getProductBySlug');

// Exams
Route::apiResource('examen', 'App\Http\Controllers\ExamController', [
  'only' => ['index', 'show'],
]);
Route::get('exams/course/{id}', 'App\Http\Controllers\ExamController@getExamByCourseId');

// Categories
Route::apiResource('category', 'App\Http\Controllers\CategoryController',[
  'only' => ['index', 'show'],
]);

// Tags
Route::apiResource('tag', 'App\Http\Controllers\TagController', [
  'only' => ['index', 'show'],
]);


// school
Route::apiResource('school', 'App\Http\Controllers\SchoolController',[
    'only' => ['index', 'show'],
]);
Route::post('school', 'App\Http\Controllers\SchoolController@store');
Route::get('school/slug/{slug}', 'App\Http\Controllers\SchoolController@getSchoolBySlug');



// Order Status
Route::apiResource('orderStatus', 'App\Http\Controllers\OrderStatusController',[
  'only' => ['index', 'show'],
]);

// Blogs
Route::apiResource('blog', 'App\Http\Controllers\BlogController', [
  'only' => ['index', 'show'],
]);
Route::get('blog/slug/{slug}', 'App\Http\Controllers\BlogController@getBlogsBySlug');

// Pages
Route::apiResource('page', 'App\Http\Controllers\PageController', [
  'only' => ['index', 'show'],
]);
Route::get('page/slug/{slug}', 'App\Http\Controllers\PageController@getPagesBySlug');

// Taxes
Route::apiResource('tax', 'App\Http\Controllers\TaxController', [
  'only' => ['index', 'show'],
]);

// Coupons
Route::apiResource('coupon', 'App\Http\Controllers\CouponController', [
  'only' => ['index', 'show'],
]);

// Currencies
Route::apiResource('currency', 'App\Http\Controllers\CurrencyController', [
  'only' => ['index', 'show'],
]);

// Faqs
Route::apiResource('faq', 'App\Http\Controllers\FaqController', [
  'only' => ['index', 'show'],
]);

// Home
Route::apiResource('home', 'App\Http\Controllers\HomePageController', [
  'only' => ['index', 'show'],
]);

// Theme
Route::apiResource('theme', 'App\Http\Controllers\ThemeController',[
  'only' => ['index', 'show'],
]);

// Products
Route::apiResource('question-and-answer', 'App\Http\Controllers\QuestionAndAnswerController',[
  'only' => ['index', 'show'],
]);

// Reviews
Route::get('front/review', 'App\Http\Controllers\ReviewController@frontIndex');

// ContactUs
Route::post('/contact-us', 'App\Http\Controllers\ContactUsController@contactUs');


Route::group(['middleware' => ['localization','auth:sanctum']], function () {


    // Authentication
  Route::post('logout', 'App\Http\Controllers\AuthController@logout');

  // Account
  Route::get('self', 'App\Http\Controllers\AccountController@self');
  Route::put('updateProfile', 'App\Http\Controllers\AccountController@updateProfile');
  Route::put('updatePassword', 'App\Http\Controllers\AccountController@updatePassword');
  Route::put('updateProfile', 'App\Http\Controllers\AccountController@updateProfile');
  Route::put('updatePassword', 'App\Http\Controllers\AccountController@updatePassword');
  Route::put('updateStoreProfile', 'App\Http\Controllers\AccountController@updateStoreProfile');

  // Address
  Route::apiResource('address', 'App\Http\Controllers\AddressController');

  // Payment Account
  Route::apiResource('paymentAccount', 'App\Http\Controllers\PaymentAccountController');

  // Badge
  Route::get('badge','App\Http\Controllers\BadgeController@index');


  // Notifications
  Route::get('notifications', 'App\Http\Controllers\NotificationController@index');
  Route::put('notifications/markAsRead', 'App\Http\Controllers\NotificationController@markAsRead');
  Route::put('notifications/change-params/{device_id}', 'App\Http\Controllers\NotificationController@changeParams');
  Route::delete('notifications/{id}', 'App\Http\Controllers\NotificationController@destroy');

  // ***********  Frontend   ***********
  Route::apiResource('cart', 'App\Http\Controllers\CartController');
  Route::apiResource('refund', 'App\Http\Controllers\RefundController');
  Route::apiResource('compare', 'App\Http\Controllers\CompareController');
  Route::apiResource('wishlist', 'App\Http\Controllers\WishlistController');

  Route::put('cart', 'App\Http\Controllers\CartController@update');
  Route::post('sync/cart', 'App\Http\Controllers\CartController@sync');
  Route::put('replace/cart', 'App\Http\Controllers\CartController@replace');

  // **********************  Backend   *******************************

  // Dashboard
  Route::get('statistics/count', 'App\Http\Controllers\DashboardController@index');
  Route::get('dashboard/chart', 'App\Http\Controllers\DashboardController@chart');

  // Users
  Route::apiResource('user', 'App\Http\Controllers\UserController');

  Route::put('user/{id}/{status}', 'App\Http\Controllers\UserController@status')->middleware('can:user.edit');
  Route::post('user/csv/import', 'App\Http\Controllers\UserController@import')->middleware('can:user.create');
  Route::post('user/csv/export', 'App\Http\Controllers\UserController@export')->name('users.export')->middleware('can:user.index');
  Route::post('user/deleteAll', 'App\Http\Controllers\UserController@deleteAll')->middleware('can:user.destroy');
  Route::delete('user/address/{id}', 'App\Http\Controllers\UserController@deleteAddress')->middleware('can:user.edit');
   Route::post('user/save-player-id', 'App\Http\Controllers\UserController@savePlayerId');

  //Roles
  Route::apiResource('role', 'App\Http\Controllers\RoleController');
  Route::get('module', 'App\Http\Controllers\RoleController@modules');
  Route::post('role/deleteAll', 'App\Http\Controllers\RoleController@deleteAll')->middleware('can:role.destroy');

  // course
  Route::apiResource('course', 'App\Http\Controllers\CourseController', [
    'only' => ['store', 'update', 'destroy','create'],
  ]);
  Route::post('course/replicate', 'App\Http\Controllers\CourseController@replicate');
  Route::put('course/{id}/{status}', 'App\Http\Controllers\CourseController@status');
  Route::post('course/csv/export', 'App\Http\Controllers\CourseController@export')->name('courses.export');
  Route::post('course/csv/import', 'App\Http\Controllers\CourseController@import');
  Route::put('course/approve/{id}/{status}', 'App\Http\Controllers\CourseController@approve');
  Route::post('course/deleteAll', 'App\Http\Controllers\CourseController@deleteAll');

  // Exams
  Route::apiResource('examen', 'App\Http\Controllers\ExamController', [
    'only' => ['store', 'update', 'destroy', 'create'],
  ]);
  Route::post('examen/replicate', 'App\Http\Controllers\ExamController@replicate');
  Route::put('examen/{id}/{status}', 'App\Http\Controllers\ExamController@status');
  Route::post('examen/deleteAll', 'App\Http\Controllers\ExamController@deleteAll');

  // Attributes & Attribute Values
  Route::apiResource('attribute', 'App\Http\Controllers\AttributeController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::apiResource('attribute-value', 'App\Http\Controllers\AttributeValueController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::put('attribute/{id}/{status}', 'App\Http\Controllers\AttributeController@status')->middleware('can:attribute.edit');
  Route::post('attribute/csv/import', 'App\Http\Controllers\AttributeController@import')->middleware('can:attribute.create');
  Route::post('attribute/csv/export', 'App\Http\Controllers\AttributeController@export')->name('attributes.export')->middleware('can:attribute.index');
  Route::post('attribute/deleteAll', 'App\Http\Controllers\AttributeController@deleteAll')->middleware('can:attribute.destroy');

  // Categories
  Route::apiResource('category', 'App\Http\Controllers\CategoryController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('category/csv/import', 'App\Http\Controllers\CategoryController@import')->middleware('can:category.create');
  Route::post('category/csv/export', 'App\Http\Controllers\CategoryController@export')->name('categories.export')->middleware('can:category.index');
  Route::put('category/{id}/{status}', 'App\Http\Controllers\CategoryController@status')->middleware('can:category.edit');

  // Tags
  Route::apiResource('tag', 'App\Http\Controllers\TagController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('tag/csv/import', 'App\Http\Controllers\TagController@import')->middleware('can:tag.create');
  Route::post('tag/csv/export', 'App\Http\Controllers\TagController@export')->name('tags.export')->middleware('can:tag.index');
  Route::post('tag/deleteAll', 'App\Http\Controllers\TagController@deleteAll')->middleware('can:tag.destroy');
  Route::put('tag/{id}/{status}', 'App\Http\Controllers\TagController@status')->middleware('can:tag.edit');


    // Companys
    Route::apiResource('school', 'App\Http\Controllers\SchoolController',[
        'only' => ['update', 'destroy'],
    ]);
    Route::post('school/deleteAll', 'App\Http\Controllers\SchoolController@deleteAll')->middleware('can:store.destroy');
    Route::put('school/approve/{id}/{status}', 'App\Http\Controllers\SchoolController@approve')->middleware('can:store.edit');
    Route::put('school/{id}/{status}', 'App\Http\Controllers\SchoolController@status')->middleware('can:store.edit');



    // Orders
  Route::apiResource('order', 'App\Http\Controllers\OrderController');
  Route::post('checkout','App\Http\Controllers\CheckoutController@verifyCheckout');
  Route::post('rePayment', 'App\Http\Controllers\OrderController@rePayment');
  Route::get('trackOrder/{order_number}', 'App\Http\Controllers\OrderController@trackOrder');
  Route::get('verifyPayment/{order_number}', 'App\Http\Controllers\OrderController@verifyPayment');

  // Order Status
  Route::apiResource('orderStatus', 'App\Http\Controllers\OrderStatusController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('orderStatus/deleteAll', 'App\Http\Controllers\OrderStatusController@deleteAll')->middleware('can:order_status.destroy');
  Route::put('orderStatus/{id}/{status}', 'App\Http\Controllers\OrderStatusController@status')->middleware('can:order_status.edit');

  // Attachments
  Route::apiResource('attachment', 'App\Http\Controllers\AttachmentController');
  Route::post('attachment/deleteAll', 'App\Http\Controllers\AttachmentController@deleteAll')->middleware('can:attachment.destroy');

  // Blogs
  Route::apiResource('blog', 'App\Http\Controllers\BlogController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('blog/deleteAll', 'App\Http\Controllers\BlogController@deleteAll')->middleware('can:blog.destroy');
  Route::put('blog/{id}/{status}', 'App\Http\Controllers\BlogController@status')->middleware('can:blog.edit');

  // Pages
  Route::apiResource('page', 'App\Http\Controllers\PageController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('page/deleteAll', 'App\Http\Controllers\PageController@deleteAll')->middleware('can:page.destroy');
  Route::put('page/{id}/{status}', 'App\Http\Controllers\PageController@status')->middleware('can:page.edit');

  // Tax
  Route::apiResource('tax', 'App\Http\Controllers\TaxController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('tax/deleteAll', 'App\Http\Controllers\TaxController@deleteAll')->middleware('can:tax.destroy');
  Route::put('tax/{id}/{status}', 'App\Http\Controllers\TaxController@status')->middleware('can:tax.edit');

    // Claas
    Route::apiResource('class', 'App\Http\Controllers\ClassController',[
        'only' => ['store', 'update', 'destroy','index'],
    ]);

  // Shipping
  Route::apiResource('shipping', 'App\Http\Controllers\ShippingController');
  Route::put('shipping/{id}/{status}', 'App\Http\Controllers\ShippingController@status')->middleware('can:shipping.edit');

  // Shipping Rule
  Route::apiResource('shippingRule', 'App\Http\Controllers\ShippingRuleController');
  Route::put('shippingRule/{id}/{status}', 'App\Http\Controllers\ShippingRuleController@status')->middleware('can:shipping.edit');

  // Coupon
  Route::apiResource('coupon', 'App\Http\Controllers\CouponController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::put('coupon/{id}/{status}', 'App\Http\Controllers\CouponController@status')->middleware('can:coupon.edit');
  Route::post('coupon/deleteAll', 'App\Http\Controllers\CouponController@deleteAll')->middleware('can:coupon.destroy');

  // Currencies
  Route::apiResource('currency', 'App\Http\Controllers\CurrencyController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::put('currency/{id}/{status}', 'App\Http\Controllers\CurrencyController@status')->middleware('can:currency.edit');
  Route::post('currency/deleteAll', 'App\Http\Controllers\CurrencyController@deleteAll')->middleware('can:currency.destroy');

  // Reviews
  Route::apiResource('review', 'App\Http\Controllers\ReviewController');
  Route::post('review/deleteAll', 'App\Http\Controllers\ReviewController@deleteAll')->middleware('can:review.destroy');

  // faqs
  Route::apiResource('faq', 'App\Http\Controllers\FaqController',[
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::put('faq/{id}/{status}', 'App\Http\Controllers\FaqController@status')->middleware('can:faq.edit');
  Route::post('faq/deleteAll', 'App\Http\Controllers\FaqController@deleteAll')->middleware('can:faq.destroy');

  // Quention And Answer
  Route::apiResource('question-and-answer', 'App\Http\Controllers\QuestionAndAnswerController', [
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('question-and-answer/feedback', 'App\Http\Controllers\QuestionAndAnswerController@feedback')->middleware('can:question_and_answer.create');



  // Home
  Route::apiResource('home', 'App\Http\Controllers\HomePageController', [
    'only' => ['update'],
  ]);

  // Settings
  Route::put('settings', 'App\Http\Controllers\SettingController@update')->middleware('can:setting.edit');

});

// Quizzes
Route::apiResource('quiz', 'App\Http\Controllers\QuizController', [
  'only' => ['index', 'show'],
]);
Route::get('quizzes/course/{id}', 'App\Http\Controllers\QuizController@getQuizByCourseId');

// Protected Quiz routes
Route::group(['middleware' => ['auth:sanctum']], function () {
  Route::apiResource('quiz', 'App\Http\Controllers\QuizController', [
    'only' => ['store', 'update', 'destroy'],
  ]);
});

// Exams
Route::apiResource('exam', 'App\Http\Controllers\API\ExamController', [
  'only' => ['index', 'show'],
]);
Route::get('exams/course/{id}', 'App\Http\Controllers\API\ExamController@getExamByCourseId');

// Protected Exam routes
Route::group(['middleware' => ['auth:sanctum']], function () {
  Route::apiResource('exam', 'App\Http\Controllers\API\ExamController', [
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('exam/replicate', 'App\Http\Controllers\API\ExamController@replicate');
});

// Questions
Route::apiResource('question', 'App\Http\Controllers\API\QuestionController', [
  'only' => ['index', 'show'],
]);

// Protected Question routes
Route::group(['middleware' => ['auth:sanctum']], function () {
  Route::apiResource('question', 'App\Http\Controllers\API\QuestionController', [
    'only' => ['store', 'update', 'destroy'],
  ]);
  Route::post('questions/bulk', 'App\Http\Controllers\API\QuestionController@bulkStore');
});

// Student Enrollment & Learning Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
  // Enrollment
  Route::post('courses/enroll', 'App\Http\Controllers\API\EnrollmentController@enroll');
  Route::get('my-courses', 'App\Http\Controllers\API\EnrollmentController@myCourses');
  Route::get('course/{id}/progress', 'App\Http\Controllers\API\EnrollmentController@courseProgress');
  Route::get('course/{id}/content', 'App\Http\Controllers\API\EnrollmentController@courseContent');
  Route::post('enrollment/{id}/cancel', 'App\Http\Controllers\API\EnrollmentController@cancel');
  
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
  
  // Revenue & Financial Information
  Route::get('instructor/revenues', 'App\Http\Controllers\API\RevenueController@instructorSummary');
  Route::get('course/{id}/revenues', 'App\Http\Controllers\API\RevenueController@courseRevenues');
  Route::get('charity/contributions', 'App\Http\Controllers\API\RevenueController@charityContributions');

  // Get all completed quiz and exam results
  Route::get('/completed-results', [EnrollmentController::class, 'completedResults']);
});

// Get user enrollments
Route::middleware('auth:sanctum')->get('/enrollments', function (Request $request) {
    $user = $request->user();
    $enrollments = \App\Models\Enrollment::with(['course.media','course.category','course.instructor'])
        ->where('user_id', $user->id)
        ->get();

    return response()->json([
        'enrollments' => $enrollments,
        'total' => $enrollments->count()
    ]);
});
