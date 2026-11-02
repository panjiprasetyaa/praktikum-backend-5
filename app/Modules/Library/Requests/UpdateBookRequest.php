<?php

namespace Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book')?->id ?? $this->route('book');
        $currentYear = (int) date('Y');

        return [
            'judul' => ['sometimes', 'string', 'max:255'],
            'isbn' => [
                'sometimes',
                'string',
                Rule::unique('books', 'isbn')->ignore($bookId),
                'regex:/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/',
            ],
            'tahun_terbit' => ['sometimes', 'integer', 'min:1900', 'max:' . $currentYear],
            'harga' => ['sometimes', 'numeric', 'min:0'],
            'stok' => ['sometimes', 'integer', 'min:0'],
            'author_id' => ['sometimes', 'integer', 'exists:authors,id'],
        ];
    }

    public function messages(): array
    {
        $currentYear = date('Y');

        return [
            'judul.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.unique' => 'ISBN sudah terdaftar pada buku lain.',
            'isbn.regex' => 'Format ISBN tidak valid (harus 10 atau 13 digit angka).',
            'tahun_terbit.integer' => 'Tahun terbit harus berupa bilangan bulat.',
            'tahun_terbit.min' => 'Tahun terbit minimal tahun 1900.',
            'tahun_terbit.max' => "Tahun terbit tidak boleh melebihi tahun saat ini ({$currentYear}).",
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'author_id.exists' => 'Penulis yang dipilih tidak valid.',
        ];
    }
}
