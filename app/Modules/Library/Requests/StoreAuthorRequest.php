<?php

namespace Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:authors,email'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama penulis wajib diisi.',
            'nama.max' => 'Nama penulis maksimal 255 karakter.',
            'email.required' => 'Email penulis wajib diisi.',
            'email.email' => 'Format email penulis tidak valid.',
            'email.unique' => 'Email penulis sudah terdaftar.',
            'bio.max' => 'Bio penulis maksimal 1000 karakter.',
        ];
    }
}
