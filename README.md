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


# API Documentation

This document contains all the API routes available in the application and instructions for testing them using Postman.

---

## Authentication Routes
| Method | Endpoint          | Description               |
|--------|-------------------|---------------------------|
| POST   | `/login`          | Login a user             |
| POST   | `/register`       | Register a new user      |
| POST   | `/forgot-password`| Request password reset   |
| POST   | `/verify-token`   | Verify reset token       |
| POST   | `/update-password`| Update user password     |
| POST   | `/logout`         | Logout the user          |

---

## User Routes
| Method | Endpoint                  | Description                       |
|--------|---------------------------|-----------------------------------|
| GET    | `/user`                   | Get user details                 |
| POST   | `/user/csv/import`        | Import users via CSV             |
| POST   | `/user/csv/export`        | Export users to CSV              |
| POST   | `/user/deleteAll`         | Delete all users                 |
| PUT    | `/user/{id}/{status}`     | Update user status               |
| DELETE | `/user/address/{id}`      | Delete user address              |
| POST   | `/user/save-player-id`    | Save player ID                   |

---

## Course Routes
| Method | Endpoint                  | Description                       |
|--------|---------------------------|-----------------------------------|
| GET    | `/course`                 | Get all courses                  |
| GET    | `/course/{id}`            | Get course details               |
| POST   | `/course`                 | Create a new course              |
| PUT    | `/course/{id}`            | Update course details            |
| POST   | `/course/csv/import`      | Import courses via CSV           |
| POST   | `/course/csv/export`      | Export courses to CSV            |
| POST   | `/course/replicate`       | Replicate a course               |
| POST   | `/course/deleteAll`       | Delete all courses               |
| PUT    | `/course/approve/{id}/{status}` | Approve or reject a course |
| PUT    | `/course/{id}/{status}`   | Update course status             |

---

## Testing the APIs in Postman

1. **Import the API Collection**:
   - Open Postman.
   - Click on "Import" in the top-left corner.
   - Import the API collection file (you can create one manually or export it from your Laravel application).

2. **Set Up Environment Variables**:
   - Create a new environment in Postman.
   - Add variables like `base_url` (e.g., `http://localhost:8000/api`) and `token` (for authentication).

3. **Testing Endpoints**:
   - For endpoints requiring authentication, include the `Authorization` header with the Bearer token:
     ```
     Authorization: Bearer {{token}}
     ```
   - Replace placeholders in the URL (e.g., `{id}`) with actual values.

4. **Example Request**:
   - **Endpoint**: `/login`
   - **Method**: POST
   - **Headers**:
     ```
     Content-Type: application/json
     ```
   - **Body** (JSON):
     ```json
     {
       "email": "user@example.com",
       "password": "password123"
     }
     ```

5. **Save Responses**:
   - Save successful responses in Postman for future reference.

---

### **Roadmap for Creating a Course with Chapters, Quizzes, and Exams**

#### **Step 1: Create a Course**
1. **Endpoint**: `/course`
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "title": "Introduction to Programming",
     "description": "Learn the basics of programming.",
     "slug": "introduction-to-programming",
     "price": 100,
     "sale_price": 80,
     "level": "beginner",
     "language": "en",
     "duration": 120,
     "requirements": "Basic computer knowledge",
     "what_you_will_learn": "Understand programming fundamentals",
     "is_featured": true,
     "is_published": true,
     "status": "published",
     "max_students": 50,
     "category_id": 1,
     "user_id": 1, // Instructor ID
     "media_id": 10, // Course image ID
     "video_id": 20 // Intro video ID
   }
   ```
5. **Response**: Note the `id` of the created course for the next steps.

---

#### **Step 2: Add Chapters to the Course**
1. **Endpoint**: `/chapter`
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "course_id": 1, // Replace with the course ID
     "title": "Chapter 1: Getting Started",
     "serial_number": 1, // Order of the chapter
     "contents": [
       {
         "title": "Introduction Video",
         "type": "video",
         "duration": 10 // Duration in minutes
       },
       {
         "title": "Chapter Notes",
         "type": "text",
         "content": "Detailed notes for the chapter."
       }
     ]
   }
   ```
5. **Response**: Note the `id` of the created chapter.

---

#### **Step 3: Create a Quiz for the Course**
1. **Endpoint**: `  `
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "course_id": 1, // Replace with the course ID
     "title": "Quiz 1: Basics of Programming",
     "questions": [
       {
         "question": "What is a variable?",
         "options": ["A constant value", "A storage location", "A function", "None of the above"],
         "correct_option": 1
       },
       {
         "question": "Which data type is used to store text?",
         "options": ["int", "float", "string", "boolean"],
         "correct_option": 2
       }
     ]
   }
   ```
5. **Response**: Note the `id` of the created quiz.

---

#### **Step 4: Create an Exam for the Course**
1. **Endpoint**: `/examen`
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "course_id": 1, // Replace with the course ID
     "title": "Final Exam: Programming Basics",
     "multi_chance": true, // Allow multiple attempts
     "questions": [
       {
         "question": "Explain the difference between a variable and a constant.",
         "type": "text"
       },
       {
         "question": "Write a program to print 'Hello, World!' in Python.",
         "type": "code"
       }
     ]
   }
   ```
5. **Response**: Note the `id` of the created exam.

---

#### **Step 5: Enroll a User in the Course**
1. **Endpoint**: `/save-enrollment`
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "user_id": 1, // Replace with the user ID
     "course_id": 1 // Replace with the course ID
   }
   ```

---

#### **Step 6: Track Progress**
1. **Endpoint**: `/enrolement/progress/update`
2. **Method**: `POST`
3. **Headers**:
   ```
   Authorization: Bearer {{token}}
   Content-Type: application/json
   ```
4. **Body** (JSON):
   ```json
   {
     "user_id": 1, // Replace with the user ID
     "course_id": 1, // Replace with the course ID
     "progress": 50 // Progress percentage
   }
   ```

---

#### **Step 7: Verify Results**
- **Get Course Details**:
  - **Endpoint**: `/course/{id}`
  - **Method**: `GET`
  - Replace `{id}` with the course ID.
- **Get Quiz Details**:
  - **Endpoint**: `/quiz/{id}`
  - **Method**: `GET`
  - Replace `{id}` with the quiz ID.
- **Get Exam Details**:
  - **Endpoint**: `/examen/{id}`
  - **Method**: `GET`
  - Replace `{id}` with the exam ID.

---

This roadmap provides a step-by-step guide to creating a course, adding chapters, quizzes, and exams, and testing them in Postman. Let me know if you need further clarifications or adjustments!
"# gold-lms-backend"
