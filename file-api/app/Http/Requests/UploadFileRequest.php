<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'type' => 'required|in:passport,visa,photo',
        ];
    }

    public function messages(): array {
        return [
            'file.mimes' => 'Only PDF, PNG, and JPG files are allowed.',
            'file.max' => 'Maximum allowed file size is 4MB.',
            'type.in' => 'Type must be one of: passport, visa, photo.',
        ];
    }
}
