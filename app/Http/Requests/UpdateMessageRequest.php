<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('put')) {
            return [
            'text' => ['required', 'string'],
            'type' => ['required', 'string', 'max:50']
        ];
        }

        return [
            'text' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', 'string', 'max:50']
        ];
    }
}
