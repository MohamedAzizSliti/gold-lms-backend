<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Question;

class ExamSeeder extends Seeder
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

        $exams = [
            // HTML & CSS Final Exam
            [
                'title' => 'HTML & CSS Final Certification Exam',
                'description' => 'Comprehensive exam covering all HTML and CSS topics',
                'duration' => 60,
                'mark_per_question' => 5,
                'pass_marks' => 80,
                'multi_chance' => false,
                'course_id' => $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id ?? $courses->first()->id,
                'questions' => [
                    [
                        'question_text' => 'Which CSS property is used to create a flexbox container?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'display: flex', 'is_correct' => true],
                            'option_2' => ['text' => 'flex: container', 'is_correct' => false],
                            'option_3' => ['text' => 'layout: flex', 'is_correct' => false],
                            'option_4' => ['text' => 'position: flex', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'What is the correct HTML structure for a table?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => '<table><tr><td></td></tr></table>', 'is_correct' => true],
                            'option_2' => ['text' => '<table><row><cell></cell></row></table>', 'is_correct' => false],
                            'option_3' => ['text' => '<table><td><tr></tr></td></table>', 'is_correct' => false],
                            'option_4' => ['text' => '<table><column><row></row></column></table>', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which CSS units are relative?',
                        'question_type' => 'multiple_choice',
                        'options' => [
                            'option_1' => ['text' => 'em', 'is_correct' => true],
                            'option_2' => ['text' => 'rem', 'is_correct' => true],
                            'option_3' => ['text' => 'px', 'is_correct' => false],
                            'option_4' => ['text' => '%', 'is_correct' => true],
                        ],
                        'correct_option' => 'option_1,option_2,option_4'
                    ]
                ]
            ],

            // JavaScript Final Exam
            [
                'title' => 'JavaScript ES6 Certification Exam',
                'description' => 'Advanced JavaScript and ES6 features examination',
                'duration' => 90,
                'mark_per_question' => 4,
                'pass_marks' => 75,
                'multi_chance' => true,
                'course_id' => $courses->where('title', 'JavaScript ES6 Moderne')->first()?->id ?? $courses->skip(1)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'What is the difference between let and var?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'let has block scope, var has function scope', 'is_correct' => true],
                            'option_2' => ['text' => 'let has function scope, var has block scope', 'is_correct' => false],
                            'option_3' => ['text' => 'No difference', 'is_correct' => false],
                            'option_4' => ['text' => 'let is faster than var', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which methods can be used with Promises?',
                        'question_type' => 'multiple_choice',
                        'options' => [
                            'option_1' => ['text' => '.then()', 'is_correct' => true],
                            'option_2' => ['text' => '.catch()', 'is_correct' => true],
                            'option_3' => ['text' => '.finally()', 'is_correct' => true],
                            'option_4' => ['text' => '.done()', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1,option_2,option_3'
                    ],
                    [
                        'question_text' => 'What does the spread operator (...) do?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'Spreads array elements', 'is_correct' => true],
                            'option_2' => ['text' => 'Creates a new array', 'is_correct' => false],
                            'option_3' => ['text' => 'Deletes array elements', 'is_correct' => false],
                            'option_4' => ['text' => 'Sorts array elements', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ]
                ]
            ],

            // React Final Exam
            [
                'title' => 'React.js Certification Exam',
                'description' => 'Complete React.js knowledge assessment',
                'duration' => 75,
                'mark_per_question' => 3,
                'pass_marks' => 80,
                'multi_chance' => false,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id ?? $courses->skip(2)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'What is the Virtual DOM in React?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'A JavaScript representation of the real DOM', 'is_correct' => true],
                            'option_2' => ['text' => 'A new browser API', 'is_correct' => false],
                            'option_3' => ['text' => 'A React component', 'is_correct' => false],
                            'option_4' => ['text' => 'A CSS framework', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which lifecycle methods are available in functional components?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'componentDidMount, componentWillUnmount', 'is_correct' => false],
                            'option_2' => ['text' => 'useEffect hook replaces lifecycle methods', 'is_correct' => true],
                            'option_3' => ['text' => 'render method only', 'is_correct' => false],
                            'option_4' => ['text' => 'All class component lifecycle methods', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_2'
                    ],
                    [
                        'question_text' => 'What are React Hooks?',
                        'question_type' => 'multiple_choice',
                        'options' => [
                            'option_1' => ['text' => 'Functions that let you use state in functional components', 'is_correct' => true],
                            'option_2' => ['text' => 'Functions that let you use lifecycle methods', 'is_correct' => true],
                            'option_3' => ['text' => 'Only available in class components', 'is_correct' => false],
                            'option_4' => ['text' => 'Start with "use" prefix', 'is_correct' => true],
                        ],
                        'correct_option' => 'option_1,option_2,option_4'
                    ]
                ]
            ],

            // Python Final Exam
            [
                'title' => 'Python Programming Certification',
                'description' => 'Comprehensive Python programming assessment',
                'duration' => 120,
                'mark_per_question' => 3,
                'pass_marks' => 70,
                'multi_chance' => true,
                'course_id' => $courses->where('title', 'Python Programming')->first()?->id ?? $courses->skip(3)->first()->id,
                'questions' => [
                    [
                        'question_text' => 'What is the correct way to define a function in Python?',
                        'question_type' => 'single_choice',
                        'options' => [
                            'option_1' => ['text' => 'def function_name():', 'is_correct' => true],
                            'option_2' => ['text' => 'function function_name():', 'is_correct' => false],
                            'option_3' => ['text' => 'func function_name():', 'is_correct' => false],
                            'option_4' => ['text' => 'define function_name():', 'is_correct' => false],
                        ],
                        'correct_option' => 'option_1'
                    ],
                    [
                        'question_text' => 'Which data types are mutable in Python?',
                        'question_type' => 'multiple_choice',
                        'options' => [
                            'option_1' => ['text' => 'list', 'is_correct' => true],
                            'option_2' => ['text' => 'dict', 'is_correct' => true],
                            'option_3' => ['text' => 'tuple', 'is_correct' => false],
                            'option_4' => ['text' => 'set', 'is_correct' => true],
                        ],
                        'correct_option' => 'option_1,option_2,option_4'
                    ]
                ]
            ]
        ];

        foreach ($exams as $examData) {
            $questions = $examData['questions'];
            unset($examData['questions']);

            $exam = Exam::create($examData);

            foreach ($questions as $questionData) {
                $options = $questionData['options'];
                unset($questionData['options']);
                
                $questionData['exam_id'] = $exam->id;
                $questionData['course_id'] = $exam->course_id;
                
                // Add individual option fields
                $questionData['option_1'] = json_encode($options['option_1']);
                $questionData['option_2'] = json_encode($options['option_2']);
                $questionData['option_3'] = json_encode($options['option_3']);
                $questionData['option_4'] = json_encode($options['option_4']);

                Question::create($questionData);
            }
        }

        $this->command->info('Exam data seeded successfully!');
    }
}
