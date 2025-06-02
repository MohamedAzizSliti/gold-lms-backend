<?php

namespace App\Repositories\Eloquents;

use App\Enums\QuestionTypeEnum;
use App\Models\Question;

class QuestionRepository extends Repository
{
    public static function model()
    {
        return Question::class;
    }

    public static function deserializeOptions(array $requestQuestion)
    {
        $options = [];

        switch ($requestQuestion['question_type']) {
            case QuestionTypeEnum::MULTIPLE_CHOICE->value:
                $options = [
                    'option_1' => [
                        'text' => $requestQuestion['option_1']['text'],
                        'is_correct' => isset($requestQuestion['option_1']['is_correct']) ? $requestQuestion['option_1']['is_correct'] : false
                    ],
                    'option_2' => [
                        'text' => $requestQuestion['option_2']['text'],
                        'is_correct' => isset($requestQuestion['option_2']['is_correct']) ? $requestQuestion['option_2']['is_correct'] : false
                    ],
                    'option_3' => [
                        'text' => $requestQuestion['option_3']['text'],
                        'is_correct' => isset($requestQuestion['option_3']['is_correct'])  ? $requestQuestion['option_3']['is_correct'] : false
                    ],
                    'option_4' => [
                        'text' => $requestQuestion['option_4']['text'],
                        'is_correct' => isset($requestQuestion['option_4']['is_correct']) ? $requestQuestion['option_4']['is_correct'] : false
                    ]
                ];
                break;
            case QuestionTypeEnum::SINGLE_CHOICE->value:
                $options = [
                    'option_1' => [
                        'text' => $requestQuestion['option_1']['text'],
                        'is_correct' => false
                    ],
                    'option_2' => [
                        'text' => $requestQuestion['option_2']['text'],
                        'is_correct' => false
                    ],
                    'option_3' => [
                        'text' => $requestQuestion['option_3']['text'] ?? null,
                        'is_correct' => false
                    ],
                    'option_4' => [
                        'text' => $requestQuestion['option_4']['text'] ?? null,
                        'is_correct' => false
                    ]
                ];

                $correctOption = $requestQuestion['correct_option'];
                $options[$correctOption]['is_correct'] = true;

                break;
            case QuestionTypeEnum::BINARY->value:
                $options = [
                    'yes' => [
                        'is_correct' => $requestQuestion['correct_option'] == 'yes',
                    ],
                    'no' => [
                        'is_correct' => $requestQuestion['correct_option'] == 'no',
                    ]
                ];
                break;
        }

        return $options;
    }
}
