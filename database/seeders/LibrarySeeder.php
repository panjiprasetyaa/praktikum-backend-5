<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Library\Models\Author;
use Modules\Library\Models\Book;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $author1 = Author::firstOrCreate(
            ['email' => 'andrea@hirata.com'],
            [
                'nama' => 'Andrea Hirata',
                'bio' => 'Penulis novel best seller Laskar Pelangi asal Belitung.',
            ]
        );

        $author2 = Author::firstOrCreate(
            ['email' => 'tereliye@pustaka.com'],
            [
                'nama' => 'Tere Liye',
                'bio' => 'Penulis produktif novel petualangan, fantasi, dan drama keluarga.',
            ]
        );

        $author3 = Author::firstOrCreate(
            ['email' => 'penulis.baru@contoh.com'],
            [
                'nama' => 'Penulis Mandiri (Tanpa Buku)',
                'bio' => 'Dapat digunakan untuk menguji penghapusan penulis yang berhasil.',
            ]
        );

        // Buku dengan stok tersedia (siap dipinjam)
        Book::firstOrCreate(
            ['isbn' => '978-602-03-0112-9'],
            [
                'judul' => 'Bumi (Serial Dunia Paralel)',
                'tahun_terbit' => 2014,
                'harga' => 88000,
                'stok' => 5,
                'author_id' => $author2->id,
            ]
        );

        // Buku dengan stok 0 (untuk demonstrasi BookNotAvailableException)
        Book::firstOrCreate(
            ['isbn' => '978-979-3062-79-2'],
            [
                'judul' => 'Laskar Pelangi',
                'tahun_terbit' => 2005,
                'harga' => 75000,
                'stok' => 0, // STOK KOSONG (0)
                'author_id' => $author1->id,
            ]
        );
    }
}
