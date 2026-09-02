<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare date for validatior (trim for text, trim/strlower for type)
     */

    protected function prepareForValidation(): void
    {
        $this->merge([
            'text' => trim($this->text),
            'type' => strtolower(trim($this->type))
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'user_id' => ['required', 'integer', 'exists:users,id']
        ];
    }
}
