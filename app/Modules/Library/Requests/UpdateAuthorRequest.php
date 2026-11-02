<?php

namespace Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $authorId = $this->route('author')?->id ?? $this->route('author');

        return [
            'nama' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('authors', 'email')->ignore($authorId)],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.max' => 'Nama penulis maksimal 255 karakter.',
            'email.email' => 'Format email penulis tidak valid.',
            'email.unique' => 'Email penulis sudah terdaftar.',
            'bio.max' => 'Bio penulis maksimal 1000 karakter.',
        ];
    }
}
