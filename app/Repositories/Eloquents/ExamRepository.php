<?php

namespace App\Repositories\Eloquents;

use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Exam;
use Exception;

class ExamRepository extends BaseRepository
{
    public function model()
    {
        return Exam::class;
    }

    public static function storeByRequest($request)
    {
        try {
            $exam = new Exam([
                'title' => $request->title,
                'duration' => $request->duration,
                'mark_per_question' => $request->mark_per_question ?? 1.0,
                'pass_marks' => $request->pass_marks ?? 50.0,
                'course_id' => $request->course_id,
            ]);

            $exam->save();

            if (isset($request->questions) && is_array($request->questions)) {
                foreach ($request->questions as $requestQuestion) {
                    if (!isset($requestQuestion['question'])) {
                        throw new \InvalidArgumentException('The question key is missing in the request data.');
                    }

                    $options = QuestionRepository::deserializeOptions($requestQuestion);

                    QuestionRepository::create([
                        'course_id' => $exam->course_id,
                        'exam_id' => $exam->id,
                        'question_text' => $requestQuestion['question'],
                        'question_type' => $requestQuestion['question_type'],
                        'options' =>  $options,
                    ]);
                }
            }

            return $exam;
        } catch (Exception $e) {
            \Log::error('Error creating exam: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function updateByRequest($request, Exam $exam)
    {
        return self::update($exam, [
            'title' => $request->title ?? $exam->title,
            'duration' => $request->duration ?? $exam->duration,
            'mark_per_question' => $request->mark_per_question ?? $exam->mark_per_question,
            'pass_marks' => $request->pass_marks ?? $exam->pass_marks,
        ]);
    }
}
