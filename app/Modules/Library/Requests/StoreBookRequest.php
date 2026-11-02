<?php

namespace Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'judul' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'unique:books,isbn', 'regex:/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/'],
            'tahun_terbit' => ['required', 'integer', 'min:1900', 'max:' . $currentYear],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
        ];
    }

    public function messages(): array
    {
        $currentYear = date('Y');

        return [
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.required' => 'ISBN buku wajib diisi.',
            'isbn.unique' => 'ISBN sudah terdaftar.',
            'isbn.regex' => 'Format ISBN tidak valid (harus 10 atau 13 digit angka).',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer' => 'Tahun terbit harus berupa bilangan bulat.',
            'tahun_terbit.min' => 'Tahun terbit minimal tahun 1900.',
            'tahun_terbit.max' => "Tahun terbit tidak boleh melebihi tahun saat ini ({$currentYear}).",
            'harga.required' => 'Harga buku wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'stok.required' => 'Stok buku wajib diisi.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'author_id.required' => 'Penulis wajib dipilih.',
            'author_id.exists' => 'Penulis yang dipilih tidak valid.',
        ];
    }
}
