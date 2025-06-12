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

        if (!isset($requestQuestion['question_type'])) {
            throw new \InvalidArgumentException('The question_type key is missing in the request data.');
        }

        switch ($requestQuestion['question_type']) {
            case QuestionTypeEnum::MULTIPLE_CHOICE->value:
            case QuestionTypeEnum::SINGLE_CHOICE->value:
                if (isset($requestQuestion['options']) && is_array($requestQuestion['options'])) {
                    foreach ($requestQuestion['options'] as $index => $option) {
                        $options['option_' . ($index + 1)] = [
                            'text' => $option['text'],
                            'is_correct' => $option['is_correct'] ?? false,
                        ];
                    }
                }
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
