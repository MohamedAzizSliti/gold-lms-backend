<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Course;
use App\Models\Question;

class QuizSeeder extends Seeder
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

        $quizzes = [
            // HTML & CSS Course Quizzes
            [
                'title' => 'Quiz HTML Basics',
                'description' => 'Test your knowledge of HTML fundamentals',
                'duration' => 15,
                'mark_per_question' => 2,
                'pass_marks' => 60,
                'course_id' => $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id ?? $courses->first()->id,
                'questions' => [
                    [
                        'question_text' => 'What does HTML stand for?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'HyperText Markup Language', 'is_correct' => true],
                            'option_2' => ['text' => 'High Tech Modern Language', 'is_correct' => false],
                            'option_3' => ['text' => 'Home Tool Markup Language', 'is_correct' => false],
                            'option_4' => ['text' => 'Hyperlink and Text Markup Language', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which HTML tag is used for the largest heading?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => '<h6>', 'is_correct' => false],
                            'option_2' => ['text' => '<h1>', 'is_correct' => true],
                            'option_3' => ['text' => '<heading>', 'is_correct' => false],
                            'option_4' => ['text' => '<header>', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_2'
                    ],
                    [
                        'question_text' => 'Which CSS property is used to change the text color?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'text-color', 'is_correct' => false],
                            'option_2' => ['text' => 'font-color', 'is_correct' => false],
                            'option_3' => ['text' => 'color', 'is_correct' => true],
                            'option_4' => ['text' => 'text-style', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_3'
                    ]
                ]
            ],

            // JavaScript Course Quizzes
            [
                'title' => 'JavaScript ES6 Features',
                'description' => 'Test your understanding of ES6 features',
                'duration' => 20,
                'mark_per_question' => 3,
                'pass_marks' => 70,
                'course_id' => $courses->where('title', 'JavaScript ES6 Moderne')->first()?->id ?? $courses->skip(1)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'Which of the following is a new feature in ES6?',
                        'question_type' => 'multiple_choice',
                        'options' => [
                            'option_1' => ['text' => 'Arrow functions', 'is_correct' => true],
                            'option_2' => ['text' => 'Let and const', 'is_correct' => true],
                            'option_3' => ['text' => 'Template literals', 'is_correct' => true],
                            'option_4' => ['text' => 'var keyword', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1,option_2,option_3'
                    ],
                    [
                        'question_text' => 'What is the correct syntax for an arrow function?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'function() => {}', 'is_correct' => false],
                            'option_2' => ['text' => '() => {}', 'is_correct' => true],
                            'option_3' => ['text' => '=> () {}', 'is_correct' => false],
                            'option_4' => ['text' => 'function => () {}', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_2'
                    ]
                ]
            ],

            // React Course Quizzes
            [
                'title' => 'React Components',
                'description' => 'Understanding React components and JSX',
                'duration' => 25,
                'mark_per_question' => 2.5,
                'pass_marks' => 75,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id ?? $courses->skip(2)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'What is JSX?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'JavaScript XML', 'is_correct' => true],
                            'option_2' => ['text' => 'Java Syntax Extension', 'is_correct' => false],
                            'option_3' => ['text' => 'JSON XML', 'is_correct' => false],
                            'option_4' => ['text' => 'JavaScript Extension', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which hook is used for state management in functional components?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'useEffect', 'is_correct' => false],
                            'option_2' => ['text' => 'useState', 'is_correct' => true],
                            'option_3' => ['text' => 'useContext', 'is_correct' => false],
                            'option_4' => ['text' => 'useReducer', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_2'
                    ]
                ]
            ],

            // Python Course Quiz
            [
                'title' => 'Python Fundamentals',
                'description' => 'Basic Python programming concepts',
                'duration' => 30,
                'mark_per_question' => 2,
                'pass_marks' => 65,
                'course_id' => $courses->where('title', 'Python Programming')->first()?->id ?? $courses->skip(3)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'Which of the following is the correct way to create a list in Python?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'list = []', 'is_correct' => true],
                            'option_2' => ['text' => 'list = ()', 'is_correct' => false],
                            'option_3' => ['text' => 'list = {}', 'is_correct' => false],
                            'option_4' => ['text' => 'list = ""', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'What is the output of print(type([]))?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => '<class \'list\'>', 'is_correct' => true],
                            'option_2' => ['text' => '<class \'array\'>', 'is_correct' => false],
                            'option_3' => ['text' => '<class \'tuple\'>', 'is_correct' => false],
                            'option_4' => ['text' => '<class \'dict\'>', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ]
                ]
            ]
        ];

        foreach ($quizzes as $quizData) {
            $questions = $quizData['questions'];
            unset($quizData['questions']);

            $quiz = Quiz::create($quizData);

            foreach ($questions as $questionData) {
                $options = $questionData['options'];
                unset($questionData['options']);
                
                $questionData['quiz_id'] = $quiz->id;
                $questionData['course_id'] = $quiz->course_id;
                
                // Add individual option fields
                $questionData['option_1'] = json_encode($options['option_1']);
                $questionData['option_2'] = json_encode($options['option_2']);
                $questionData['option_3'] = json_encode($options['option_3']);
                $questionData['option_4'] = json_encode($options['option_4']);

                Question::create($questionData);
            }
        }

        $this->command->info('Quiz data seeded successfully!');
    }
}
