<?php

namespace App\Http\Requests;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CreateAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {

        if (!$this->attachments) {
            throw new ExceptionHandler('Attachment is Null Value, Required Image Binary', 400);
        }

        return [
            'attachments.*' => [
                'required',
                 'mimes:jpeg,jpg,png,gif,svg,webp,mp4,mov,avi,wmv,flv,mkv,webm',
                 'max:102400' // 100MB in KB
            ]
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
