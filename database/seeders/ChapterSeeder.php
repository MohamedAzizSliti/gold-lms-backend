<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use App\Models\Course;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->info('No courses found. Please run CourseSeeder first.');
            return;
        }

        $chapters = [
            // HTML & CSS Course Chapters
            [
                'course_title' => 'Formation HTML & CSS Avancé',
                'chapters' => [
                    [
                        'title' => 'Introduction to HTML',
                        'description' => 'Learn the basics of HTML structure and syntax',
                        'order' => 1,
                        'is_published' => true,
                        'is_free' => true,
                        'duration' => 45,
                        'content' => 'HTML (HyperText Markup Language) is the standard markup language for creating web pages...'
                    ],
                    [
                        'title' => 'HTML Elements and Attributes',
                        'description' => 'Understanding HTML elements, tags, and attributes',
                        'order' => 2,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 60,
                        'content' => 'HTML elements are the building blocks of HTML pages...'
                    ],
                    [
                        'title' => 'CSS Fundamentals',
                        'description' => 'Introduction to CSS styling and selectors',
                        'order' => 3,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 75,
                        'content' => 'CSS (Cascading Style Sheets) is used to style HTML elements...'
                    ],
                    [
                        'title' => 'CSS Layout and Flexbox',
                        'description' => 'Advanced CSS layout techniques',
                        'order' => 4,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 90,
                        'content' => 'CSS Flexbox is a powerful layout method for arranging elements...'
                    ],
                    [
                        'title' => 'Responsive Design',
                        'description' => 'Creating responsive websites with CSS',
                        'order' => 5,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 80,
                        'content' => 'Responsive design ensures your website looks good on all devices...'
                    ]
                ]
            ],

            // JavaScript Course Chapters
            [
                'course_title' => 'JavaScript ES6 Moderne',
                'chapters' => [
                    [
                        'title' => 'JavaScript Basics',
                        'description' => 'Variables, data types, and basic syntax',
                        'order' => 1,
                        'is_published' => true,
                        'is_free' => true,
                        'duration' => 50,
                        'content' => 'JavaScript is a programming language that adds interactivity to websites...'
                    ],
                    [
                        'title' => 'Functions and Scope',
                        'description' => 'Understanding functions, parameters, and scope',
                        'order' => 2,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 65,
                        'content' => 'Functions are reusable blocks of code that perform specific tasks...'
                    ],
                    [
                        'title' => 'ES6 Features',
                        'description' => 'Arrow functions, let/const, template literals',
                        'order' => 3,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 70,
                        'content' => 'ES6 introduced many new features that make JavaScript more powerful...'
                    ],
                    [
                        'title' => 'Promises and Async/Await',
                        'description' => 'Asynchronous JavaScript programming',
                        'order' => 4,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 85,
                        'content' => 'Promises provide a way to handle asynchronous operations...'
                    ],
                    [
                        'title' => 'Modules and Classes',
                        'description' => 'ES6 modules and class syntax',
                        'order' => 5,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 75,
                        'content' => 'ES6 modules allow you to organize code into separate files...'
                    ]
                ]
            ],

            // React Course Chapters
            [
                'course_title' => 'React.js Fundamentals',
                'chapters' => [
                    [
                        'title' => 'Introduction to React',
                        'description' => 'What is React and why use it?',
                        'order' => 1,
                        'is_published' => true,
                        'is_free' => true,
                        'duration' => 40,
                        'content' => 'React is a JavaScript library for building user interfaces...'
                    ],
                    [
                        'title' => 'JSX and Components',
                        'description' => 'Understanding JSX syntax and React components',
                        'order' => 2,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 60,
                        'content' => 'JSX is a syntax extension for JavaScript that looks similar to HTML...'
                    ],
                    [
                        'title' => 'State and Props',
                        'description' => 'Managing component state and passing data',
                        'order' => 3,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 70,
                        'content' => 'State and props are fundamental concepts in React...'
                    ],
                    [
                        'title' => 'React Hooks',
                        'description' => 'useState, useEffect, and custom hooks',
                        'order' => 4,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 80,
                        'content' => 'Hooks allow you to use state and other React features in functional components...'
                    ],
                    [
                        'title' => 'Event Handling and Forms',
                        'description' => 'Handling user interactions in React',
                        'order' => 5,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 65,
                        'content' => 'React provides a synthetic event system for handling user interactions...'
                    ]
                ]
            ],

            // Python Course Chapters
            [
                'course_title' => 'Python Programming',
                'chapters' => [
                    [
                        'title' => 'Python Basics',
                        'description' => 'Variables, data types, and basic operations',
                        'order' => 1,
                        'is_published' => true,
                        'is_free' => true,
                        'duration' => 55,
                        'content' => 'Python is a high-level programming language known for its simplicity...'
                    ],
                    [
                        'title' => 'Control Structures',
                        'description' => 'If statements, loops, and conditional logic',
                        'order' => 2,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 70,
                        'content' => 'Control structures allow you to control the flow of your program...'
                    ],
                    [
                        'title' => 'Functions and Modules',
                        'description' => 'Creating reusable code with functions',
                        'order' => 3,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 65,
                        'content' => 'Functions help you organize code into reusable blocks...'
                    ],
                    [
                        'title' => 'Object-Oriented Programming',
                        'description' => 'Classes, objects, and inheritance',
                        'order' => 4,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 90,
                        'content' => 'Object-oriented programming is a programming paradigm based on objects...'
                    ],
                    [
                        'title' => 'File Handling and Libraries',
                        'description' => 'Working with files and external libraries',
                        'order' => 5,
                        'is_published' => true,
                        'is_free' => false,
                        'duration' => 75,
                        'content' => 'Python provides powerful tools for working with files and data...'
                    ]
                ]
            ]
        ];

        foreach ($chapters as $courseData) {
            $course = $courses->where('title', $courseData['course_title'])->first();
            
            if (!$course) {
                continue;
            }

            foreach ($courseData['chapters'] as $chapterData) {
                $chapterData['course_id'] = $course->id;
                Chapter::create($chapterData);
            }
        }

        $this->command->info('Chapter data seeded successfully!');
    }
}
