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

All endpoints are prefixed with `/api/` (e.g. `http://localhost:8000/api/`).

## Authentication
Some endpoints require authentication via Bearer Token (use `Authorization: Bearer <token>` in Postman).

---

## User Authentication

### Login
- **POST** `/login`
- **Body (JSON):**
```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

### Register
- **POST** `/register`
- **Body (JSON):**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "your_password",
  "password_confirmation": "your_password"
}
```

---

## Courses

### Get All Courses
- **GET** `/course`

### Get Course by ID
- **GET** `/course/{id}`

### Create Course (auth required)
- **POST** `/course`
- **Body (JSON):**
```json
{
  "title": "Course Title",
  "description": "Description",
  ...
}
```

### Update Course (auth required)
- **PUT** `/course/{id}`
- **Body (JSON):**
```json
{
  "title": "Updated Title"
}
```

---

## Exams

### List Exams for a Course
- **GET** `/exams/course/{course_id}`

### Create Exam
- **POST** `/examen`
- **Body (JSON):**
```json
{
  "course_id": 1,
  "title": "Final Exam"
}
```

### Get Exam by ID
- **GET** `/examen/{id}`

### Update Exam
- **PUT** `/examen/{id}`

---

## Quizzes

### List Quizzes for a Course
- **GET** `/quizzes/course/{course_id}`

### Create Quiz
- **POST** `/quiz`
- **Body (JSON):**
```json
{
  "course_id": 1,
  "title": "Quiz 1"
}
```

### Get Quiz by ID
- **GET** `/quiz/{id}`

### Update Quiz
- **PUT** `/quiz/{id}`

### Delete Quiz
- **DELETE** `/quiz/{id}`

---

## Enrollment

### Save Enrollment
- **POST** `/save-enrollment`
- **Body (JSON):**
```json
{
  "user_id": 1,
  "course_id": 2,
  "course_price": 100
}
```

### Update Enrollment Progress
- **POST** `/enrolement/progress/update`
- **Body (JSON):**
```json
{
  "enrollmentId": 1,
  "progress": 50
}
```

---

## Dashboard

### Get User Dashboard
- **GET** `/dashboard-user/{id}`

---

## Categories

### List Categories
- **GET** `/category`

---

## Blog

### List Blogs
- **GET** `/blog`

### Get Blog by Slug
- **GET** `/blog/slug/{slug}`

---

## Other Endpoints

- **GET** `/current-courses/{id}`
- **GET** `/settings`
- **GET** `/settings-app-mobile`
- **GET** `/themeOptions`
- **GET** `/rollements-course/{id}/{idUser}`
- **GET** `/page`
- **GET** `/page/slug/{slug}`
- **POST** `/contact-us`
- **GET** `/front/review`

---

## Example: Using Postman

1. Set the request type (GET/POST/PUT/etc.).
2. Set the URL, e.g. `http://localhost:8000/api/login`.
3. For POST/PUT, select "Body" → "raw" → "JSON" and paste the example JSON.
4. For protected routes, go to "Authorization" tab, select "Bearer Token" and paste your token.

---

For more endpoints (orders, users, roles, etc.), see `routes/api.php`. Most resources follow RESTful conventions (index, show, store, update, destroy). Adjust request bodies as needed for your data model.
"# gold-lms-backend" 
