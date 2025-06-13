## 
to crack it :
- go to vendor strunit StrWe.php (Routes) 

## link public to storage 
delete the storage link in public forlder 
> rm -rf public/storage
> php artisan storage:link

## permisison 
sudo chown -R www-data:www-data storage/ bootstrap/cache/
  240  sudo chmod -R 775 storage/ bootstrap/cache/
  241  tail -f storage/logs/laravel.log
  242  sudo chmod -R 775 .env

## Simulate deplacement : 
- php artisan simulate:movement 39 --steps=50 --interval=5
- php artisan simulate:movement 39 --steps=50 --interval=2 --route=circle
- php artisan simulate:movement 39 --steps=50 --interval=2 --route=line

# Errors 
## One signal rgumentCountError: Too few arguments to function Berkayk\OneSignal\OneSignalClient::__construct(), 3 passed in 
go to C:\MAMP\htdocs\_Mes Projets\Gold GPS\Project\admin\backend\vendor\laravel-notification-channels\onesignal\src
     return new OneSignalClient(
                      $oneSignalConfig['app_id'],
                      $oneSignalConfig['rest_api_key'],
                      $oneSignalConfig['user_auth_key']
                  );
 Event Traccar : 
- Edit the /opt/traccar/conf/traccar.xml
- check <entry key='event.forward.url'>https://gold-gps.bensassiridha.com/admin/api/traccar/events</entry>
- sudo systemctl restart traccar

# SMS
- I used performed solution that handle the SMS sending  that foud in this url : https://xsender.bensassiridha.com/admin   (admin/admin)
## Twilio 
you need create an new account and set the account information in xsender 
after that you should obtain or purchase a number to make it in from input rather that you can go to twilio dashboard and get triel number  (Develop > Messaging)

# Xsender 
## To Send with WhatsApp 
To send message with whatsapp you should run whatsApp node device
go to the root directory then tape : node app.js
- To mintain the process running on the background you can pm2 (npm install -g pm2)
 - pm2 start your-app.js
 - pm2 status
 - pm2 logs


# Gold LMS API Documentation

This document provides the necessary information for integrating with the Gold LMS API.

## Authentication

All API routes (except registration and login) require authentication using Laravel Sanctum.

### Registration & Login

**Register a new user**
```
POST /api/register
```
Parameters:
- name (required): User's full name
- email (required): User's email address
- password (required): Password (min 8 chars)
- password_confirmation (required): Confirm password
- role (optional): User role (default: 'student')

Response:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "created_at": "2025-06-13T10:00:00.000000Z",
    "updated_at": "2025-06-13T10:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz123456"
}
```

**Login**
```
POST /api/login
```
Parameters:
- email (required): User's email
- password (required): User's password

Response:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "created_at": "2025-06-13T10:00:00.000000Z",
    "updated_at": "2025-06-13T10:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz123456"
}
```

**Logout**
```
POST /api/logout
```

Response:
```json
{
  "message": "Successfully logged out"
}
```

## Courses

**List all courses**
```
GET /api/courses
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Laravel",
      "description": "Learn the basics of Laravel",
      "price": 49.99,
      "thumbnail": "https://example.com/images/laravel-course.jpg",
      "duration": 120,
      "instructor": {
        "id": 2,
        "name": "Jane Smith"
      },
      "category": {
        "id": 3,
        "name": "Web Development"
      },
      "created_at": "2025-06-13T10:00:00.000000Z",
      "updated_at": "2025-06-13T10:00:00.000000Z"
    },
    {...}
  ],
  "links": {...},
  "meta": {...}
}
```

**Get course details**
```
GET /api/courses/{id}
```

Response:
```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Laravel",
    "description": "Learn the basics of Laravel",
    "price": 49.99,
    "thumbnail": "https://example.com/images/laravel-course.jpg",
    "duration": 120,
    "instructor": {
      "id": 2,
      "name": "Jane Smith"
    },
    "category": {
      "id": 3,
      "name": "Web Development"
    },
    "chapters_count": 5,
    "lessons_count": 25,
    "quizzes_count": 3,
    "exams_count": 1,
    "students_count": 150,
    "created_at": "2025-06-13T10:00:00.000000Z",
    "updated_at": "2025-06-13T10:00:00.000000Z"
  }
}
```

**Get full course structure**
```
GET /api/course/{id}/structure
```

Response:
```json
{
  "course": {
    "id": 1,
    "title": "Introduction to Laravel",
    "description": "Learn the basics of Laravel",
    "price": 49.99,
    "thumbnail": "https://example.com/images/laravel-course.jpg",
    "duration": 120
  },
  "chapters": [
    {
      "id": 1,
      "title": "Getting Started",
      "position": 1,
      "lessons": [
        {
          "id": 1,
          "title": "Installation",
          "content": "How to install Laravel",
          "video_url": "https://example.com/videos/installation.mp4",
          "duration": 15,
          "position": 1
        },
        {...}
      ]
    },
    {...}
  ],
  "quizzes": [
    {
      "id": 1,
      "title": "Laravel Basics Quiz",
      "description": "Test your knowledge of Laravel basics",
      "time_limit": 15,
      "pass_percentage": 70,
      "position": 1,
      "questions_count": 10
    },
    {...}
  ],
  "exams": [
    {
      "id": 1,
      "title": "Laravel Final Exam",
      "description": "Final assessment for Laravel course",
      "time_limit": 60,
      "pass_percentage": 75,
      "position": 1,
      "questions_count": 30
    },
    {...}
  ]
}
```

## Chapters and Lessons

**List all chapters for a course**
```
GET /api/courses/{course_id}/chapters
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Getting Started",
      "position": 1,
      "lessons_count": 5,
      "total_duration": 60
    },
    {...}
  ]
}
```

**Get chapter details**
```
GET /api/chapters/{id}
```

Response:
```json
{
  "data": {
    "id": 1,
    "title": "Getting Started",
    "position": 1,
    "lessons": [
      {
        "id": 1,
        "title": "Installation",
        "content": "How to install Laravel",
        "video_url": "https://example.com/videos/installation.mp4",
        "duration": 15,
        "position": 1
      },
      {...}
    ]
  }
}
```

**Get lesson details**
```
GET /api/lessons/{id}
```

Response:
```json
{
  "data": {
    "id": 1,
    "title": "Installation",
    "content": "How to install Laravel...",
    "video_url": "https://example.com/videos/installation.mp4",
    "duration": 15,
    "position": 1,
    "chapter_id": 1,
    "course_id": 1
  }
}
```

## Quizzes and Exams

**Get quiz details**
```
GET /api/quizzes/{id}
```

Response:
```json
{
  "data": {
    "id": 1,
    "title": "Laravel Basics Quiz",
    "description": "Test your knowledge of Laravel basics",
    "time_limit": 15,
    "pass_percentage": 70,
    "position": 1,
    "course_id": 1,
    "questions": [
      {
        "id": 1,
        "question_text": "What command creates a new Laravel project?",
        "type": "multiple_choice",
        "options": [
          {"option_1": "laravel new"},
          {"option_2": "new laravel"},
          {"option_3": "create laravel"},
          {"option_4": "php laravel"}
        ],
        "points": 1
      },
      {...}
    ]
  }
}
```

**Get exam details**
```
GET /api/exams/{id}
```

Response:
```json
{
  "data": {
    "id": 1,
    "title": "Laravel Final Exam",
    "description": "Final assessment for Laravel course",
    "time_limit": 60,
    "pass_percentage": 75,
    "position": 1,
    "course_id": 1,
    "questions": [
      {
        "id": 50,
        "question_text": "Explain how middleware works in Laravel",
        "type": "short_answer",
        "points": 5
      },
      {...}
    ]
  }
}
```

## Taking Quizzes and Exams

**Start a quiz**
```
POST /api/quizzes/{quiz_id}/start
```

Response:
```json
{
  "session": {
    "id": 1,
    "quiz_id": 1,
    "user_id": 1,
    "enrollment_id": 5,
    "status": "in_progress",
    "started_at": "2025-06-13T15:30:00.000000Z",
    "time_limit": 15,
    "expires_at": "2025-06-13T15:45:00.000000Z"
  },
  "questions": [
    {
      "id": 1,
      "question_text": "What command creates a new Laravel project?",
      "type": "multiple_choice",
      "options": [
        {"option_1": "laravel new"},
        {"option_2": "new laravel"},
        {"option_3": "create laravel"},
        {"option_4": "php laravel"}
      ],
      "points": 1
    },
    {...}
  ]
}
```

**Submit quiz answer**
```
POST /api/quiz-sessions/{session_id}/answer
```
Parameters:
- question_id (required): Question ID
- answer (required): User's answer

Response:
```json
{
  "success": true,
  "message": "Answer submitted",
  "remaining_questions": 9
}
```

**Complete quiz session**
```
POST /api/quiz-sessions/{session_id}/submit
```

Response:
```json
{
  "success": true,
  "score": 80,
  "correct_answers": 8,
  "wrong_answers": 2,
  "total_questions": 10,
  "passed": true,
  "pass_percentage": 70
}
```

**Start an exam**
```
POST /api/exams/{exam_id}/start
```

Response:
```json
{
  "session": {
    "id": 1,
    "exam_id": 1,
    "user_id": 1,
    "enrollment_id": 5,
    "status": "in_progress",
    "started_at": "2025-06-13T16:00:00.000000Z",
    "time_limit": 60,
    "expires_at": "2025-06-13T17:00:00.000000Z"
  },
  "questions": [
    {
      "id": 50,
      "question_text": "Explain how middleware works in Laravel",
      "type": "short_answer",
      "points": 5
    },
    {...}
  ]
}
```

**Submit exam answer**
```
POST /api/exam-sessions/{session_id}/answer
```
Parameters:
- question_id (required): Question ID
- answer (required): User's answer

Response:
```json
{
  "success": true,
  "message": "Answer submitted",
  "remaining_questions": 29
}
```

**Complete exam session**
```
POST /api/exam-sessions/{session_id}/submit
```

Response:
```json
{
  "success": true,
  "score": 85,
  "correct_answers": 26,
  "wrong_answers": 4,
  "total_questions": 30,
  "passed": true,
  "pass_percentage": 75
}
```

## Enrollments

**Enroll in a course**
```
POST /api/courses/{course_id}/enroll
```
Parameters:
- payment_id (optional): Payment reference ID
- payment_method (optional): Payment method used

Response:
```json
{
  "success": true,
  "enrollment": {
    "id": 5,
    "user_id": 1,
    "course_id": 1,
    "status": "active",
    "enrollment_date": "2025-06-13T10:30:00.000000Z",
    "payment_id": "pay_123456",
    "payment_method": "credit_card"
  },
  "revenue": {
    "total_amount": 49.99,
    "instructor_amount": 48.49, 
    "platform_fee": 0,
    "charity_amount": 1.50
  }
}
```

**Get all enrolled courses for current user**
```
GET /api/enrollments
```

Response:
```json
{
  "data": [
    {
      "id": 5,
      "course": {
        "id": 1,
        "title": "Introduction to Laravel",
        "thumbnail": "https://example.com/images/laravel-course.jpg"
      },
      "enrollment_date": "2025-06-13T10:30:00.000000Z",
      "progress": 35,
      "status": "active"
    },
    {...}
  ]
}
```

## Progress and Results

**Get completed quiz and exam results**
```
GET /api/completed-results
```

Response:
```json
{
  "results": [
    {
      "type": "quiz",
      "id": 1,
      "session_id": 1,
      "title": "Laravel Basics Quiz",
      "course": "Introduction to Laravel",
      "course_id": 1,
      "quiz_id": 1,
      "score": 80,
      "correct_answers": 8,
      "wrong_answers": 2,
      "total_questions": 10,
      "passed": true,
      "completed_at": "2025-06-13T15:45:00.000000Z"
    },
    {
      "type": "exam",
      "id": 1,
      "session_id": 1,
      "title": "Laravel Final Exam",
      "course": "Introduction to Laravel",
      "course_id": 1,
      "exam_id": 1,
      "score": 85,
      "pass_percentage": 75,
      "correct_answers": 26,
      "wrong_answers": 4,
      "total_questions": 30,
      "passed": true,
      "completed_at": "2025-06-13T17:00:00.000000Z"
    },
    {...}
  ],
  "stats": {
    "total": 2,
    "exams_count": 1,
    "quizzes_count": 1,
    "passed_count": 2,
    "failed_count": 0
  }
}
```

**Get course progress**
```
GET /api/enrolled/course/{course_id}/progress
```

Response:
```json
{
  "overall_progress": 35,
  "completed": {
    "lessons": 8,
    "quizzes": 1,
    "exams": 0
  },
  "remaining": {
    "lessons": 17,
    "quizzes": 2,
    "exams": 1
  },
  "total": {
    "lessons": 25,
    "quizzes": 3,
    "exams": 1
  }
}
```

## Revenues and Payments

**Get instructor revenues**
```
GET /api/instructor/revenues
```

Response:
```json
{
  "total_revenue": 4849.00,
  "charity_contributions": 150.00,
  "courses": [
    {
      "id": 1,
      "title": "Introduction to Laravel",
      "enrollments": 100,
      "revenue": 4849.00,
      "charity": 150.00
    },
    {...}
  ]
}
```

**Get charity contributions**
```
GET /api/charity/contributions
```

Response:
```json
{
  "total_contributions": 150.00,
  "contributions": [
    {
      "id": 1,
      "course_id": 1,
      "course_title": "Introduction to Laravel",
      "amount": 1.50,
      "date": "2025-06-13T10:30:00.000000Z"
    },
    {...}
  ]
}
```

## Error Responses

All API errors follow this format:
```json
{
  "message": "Error message description",
  "errors": {
    "field_name": [
      "Error message for this field"
    ]
  }
}
```

Standard HTTP status codes are used:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error
"# gold-lms-backend"
