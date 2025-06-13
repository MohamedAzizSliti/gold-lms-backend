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

        // Map 'type' to 'question_type' if question_type is not present but type is
        if (!isset($requestQuestion['question_type']) && isset($requestQuestion['type'])) {
            $requestQuestion['question_type'] = $requestQuestion['type'];
        }
        
        // Set a default question_type if none is provided
        if (!isset($requestQuestion['question_type'])) {
            $requestQuestion['question_type'] = QuestionTypeEnum::MULTIPLE_CHOICE->value;
        }

        switch ($requestQuestion['question_type']) {
            case QuestionTypeEnum::MULTIPLE_CHOICE->value:
            case QuestionTypeEnum::SINGLE_CHOICE->value:
                if (isset($requestQuestion['options']) && is_array($requestQuestion['options'])) {
                    foreach ($requestQuestion['options'] as $index => $option) {
                        // Handle both object format and simple array format
                        if (is_array($option) && isset($option['text'])) {
                            $options['option_' . ($index + 1)] = [
                                'text' => $option['text'],
                                'is_correct' => $option['is_correct'] ?? false,
                            ];
                        } else {
                            $options['option_' . ($index + 1)] = [
                                'text' => $option,
                                'is_correct' => ($index === ($requestQuestion['correct_option'] ?? -1)),
                            ];
                        }
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
            default:
                // For text or code questions with no options
                $options = [];
                break;
        }

        return $options;
    }
}
