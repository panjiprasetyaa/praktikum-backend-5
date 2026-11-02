<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Modules\Library\Exceptions\AuthorHasBooksException;
use Modules\Library\Exceptions\BookNotAvailableException;
use Modules\Library\Models\Author;
use Modules\Library\Models\Book;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('1. guest cannot access library pages and is redirected to login', function () {
    $this->get('/library/books')->assertRedirect('/login');
    $this->get('/library/authors')->assertRedirect('/login');
});

test('2. authenticated user can view books and authors index', function () {
    $this->actingAs($this->user)->get('/library/books')
        ->assertStatus(200)
        ->assertSee('Daftar Buku Perpustakaan');

    $this->actingAs($this->user)->get('/library/authors')
        ->assertStatus(200)
        ->assertSee('Daftar Penulis Perpustakaan');
});

test('3. author form request validation rejects empty inputs with Indonesian messages', function () {
    $response = $this->actingAs($this->user)->post('/library/authors', []);

    $response->assertSessionHasErrors(['nama', 'email']);
    $response->assertSessionHasErrors([
        'nama' => 'Nama penulis wajib diisi.',
        'email' => 'Email penulis wajib diisi.',
    ]);
});

test('4. author form request validation rejects duplicate email', function () {
    Author::create([
        'nama' => 'Penulis Pertama',
        'email' => 'penulis@pustaka.com',
        'bio' => 'Penulis fiksi.',
    ]);

    $response = $this->actingAs($this->user)->post('/library/authors', [
        'nama' => 'Penulis Kedua',
        'email' => 'penulis@pustaka.com',
    ]);

    $response->assertSessionHasErrors(['email']);
    $response->assertSessionHasErrors([
        'email' => 'Email penulis sudah terdaftar.',
    ]);
});

test('5. author successfully created with valid data', function () {
    $response = $this->actingAs($this->user)->post('/library/authors', [
        'nama' => 'Pramoedya Ananta Toer',
        'email' => 'pram@pustaka.com',
        'bio' => 'Sastrawan terkemuka Indonesia.',
    ]);

    $response->assertRedirect('/library/authors');
    $response->assertSessionHas('success', 'Penulis berhasil ditambahkan.');
    $this->assertDatabaseHas('authors', [
        'email' => 'pram@pustaka.com',
    ]);
});

test('6. book form request validation rejects empty inputs with Indonesian messages', function () {
    $response = $this->actingAs($this->user)->post('/library/books', []);

    $response->assertSessionHasErrors(['judul', 'isbn', 'tahun_terbit', 'harga', 'stok', 'author_id']);
    $response->assertSessionHasErrors([
        'judul' => 'Judul buku wajib diisi.',
        'isbn' => 'ISBN buku wajib diisi.',
        'tahun_terbit' => 'Tahun terbit wajib diisi.',
        'harga' => 'Harga buku wajib diisi.',
        'stok' => 'Stok buku wajib diisi.',
        'author_id' => 'Penulis wajib dipilih.',
    ]);
});

test('7. book form request validation rejects invalid ISBN format', function () {
    $author = Author::create(['nama' => 'Penulis A', 'email' => 'a@a.com']);

    $response = $this->actingAs($this->user)->post('/library/books', [
        'judul' => 'Buku Salah Format',
        'isbn' => '123-abc', // format tidak valid
        'tahun_terbit' => 2020,
        'harga' => 50000,
        'stok' => 5,
        'author_id' => $author->id,
    ]);

    $response->assertSessionHasErrors(['isbn']);
    $response->assertSessionHasErrors([
        'isbn' => 'Format ISBN tidak valid (harus 10 atau 13 digit angka).',
    ]);
});

test('8. book form request rejects tahun_terbit below 1900 or future year', function () {
    $author = Author::create(['nama' => 'Penulis B', 'email' => 'b@b.com']);

    $response1 = $this->actingAs($this->user)->post('/library/books', [
        'judul' => 'Buku Kuno',
        'isbn' => '978-602-03-8594-5',
        'tahun_terbit' => 1899,
        'harga' => 50000,
        'stok' => 5,
        'author_id' => $author->id,
    ]);
    $response1->assertSessionHasErrors(['tahun_terbit']);
    $response1->assertSessionHasErrors([
        'tahun_terbit' => 'Tahun terbit minimal tahun 1900.',
    ]);

    $futureYear = (int) date('Y') + 1;
    $response2 = $this->actingAs($this->user)->post('/library/books', [
        'judul' => 'Buku Masa Depan',
        'isbn' => '978-602-03-8594-5',
        'tahun_terbit' => $futureYear,
        'harga' => 50000,
        'stok' => 5,
        'author_id' => $author->id,
    ]);
    $response2->assertSessionHasErrors(['tahun_terbit']);
});

test('9. book form request rejects negative price or stock and non-existent author', function () {
    $response = $this->actingAs($this->user)->post('/library/books', [
        'judul' => 'Buku Uji',
        'isbn' => '978-602-03-8594-5',
        'tahun_terbit' => 2022,
        'harga' => -25000,
        'stok' => -3,
        'author_id' => 99999, // author id tidak ada
    ]);

    $response->assertSessionHasErrors(['harga', 'stok', 'author_id']);
    $response->assertSessionHasErrors([
        'harga' => 'Harga tidak boleh negatif.',
        'stok' => 'Stok tidak boleh negatif.',
        'author_id' => 'Penulis yang dipilih tidak valid.',
    ]);
});

test('10. book successfully created with valid data', function () {
    $author = Author::create(['nama' => 'Tere Liye', 'email' => 'tere@liye.com']);

    $response = $this->actingAs($this->user)->post('/library/books', [
        'judul' => 'Bumi',
        'isbn' => '978-602-03-0112-9',
        'tahun_terbit' => 2014,
        'harga' => 88000,
        'stok' => 10,
        'author_id' => $author->id,
    ]);

    $response->assertRedirect('/library/books');
    $response->assertSessionHas('success', 'Buku berhasil ditambahkan.');
    $this->assertDatabaseHas('books', [
        'isbn' => '978-602-03-0112-9',
        'stok' => 10,
    ]);
});

test('11. BookNotAvailableException is thrown and logged when borrowing book with stock 0', function () {
    $author = Author::create(['nama' => 'Andrea Hirata', 'email' => 'andrea@hirata.com']);
    $book = Book::create([
        'judul' => 'Laskar Pelangi',
        'isbn' => '978-979-3062-79-2',
        'tahun_terbit' => 2005,
        'harga' => 75000,
        'stok' => 0, // stok habis
        'author_id' => $author->id,
    ]);

    // Kosongkan atau catat posisi log sebelum eksekusi
    $logPath = storage_path('logs/laravel.log');
    $initialLogContent = File::exists($logPath) ? File::get($logPath) : '';

    $response = $this->actingAs($this->user)->post("/library/books/{$book->id}/borrow");

    $response->assertSessionHas('error');
    $response->assertSessionHasErrors(['book_id']);

    // Nilai Plus: Verifikasi error dicatat di storage/logs/laravel.log
    $currentLogContent = File::exists($logPath) ? File::get($logPath) : '';
    expect($currentLogContent)->toContain('[Perpustakaan] Peminjaman gagal: Buku \'Laskar Pelangi\' tidak tersedia untuk dipinjam karena stok habis (0).');
});

test('12. borrowing book succeeds and decrements stock when stock > 0', function () {
    $author = Author::create(['nama' => 'Dewi Lestari', 'email' => 'dee@lestari.com']);
    $book = Book::create([
        'judul' => 'Supernova',
        'isbn' => '978-979-96257-0-0',
        'tahun_terbit' => 2001,
        'harga' => 95000,
        'stok' => 5,
        'author_id' => $author->id,
    ]);

    $response = $this->actingAs($this->user)->post("/library/books/{$book->id}/borrow");

    $response->assertRedirect('/library/books');
    $response->assertSessionHas('success', "Buku 'Supernova' berhasil dipinjam.");

    expect($book->fresh()->stok)->toBe(4);
    $this->assertDatabaseHas('book_loans', [
        'book_id' => $book->id,
        'user_id' => $this->user->id,
        'status' => 'borrowed',
    ]);
});

test('13. AuthorHasBooksException is thrown and logged when deleting author who has books', function () {
    $author = Author::create(['nama' => 'Habiburrahman El Shirazy', 'email' => 'kang@abik.com']);
    Book::create([
        'judul' => 'Ayat-Ayat Cinta',
        'isbn' => '978-979-3604-02-2',
        'tahun_terbit' => 2004,
        'harga' => 65000,
        'stok' => 3,
        'author_id' => $author->id,
    ]);

    $logPath = storage_path('logs/laravel.log');

    $response = $this->actingAs($this->user)->delete("/library/authors/{$author->id}");

    $response->assertSessionHas('error');
    $response->assertSessionHasErrors(['author']);

    // Penulis tidak boleh terhapus dari database
    $this->assertDatabaseHas('authors', ['id' => $author->id]);

    // Verifikasi log tercatat
    $logContent = File::get($logPath);
    expect($logContent)->toContain("[Perpustakaan] Penghapusan penulis gagal: Penulis 'Habiburrahman El Shirazy' tidak dapat dihapus karena masih memiliki 1 buku yang terdaftar.");
});

test('14. author without books can be successfully deleted', function () {
    $author = Author::create(['nama' => 'Penulis Tanpa Buku', 'email' => 'solo@pustaka.com']);

    $response = $this->actingAs($this->user)->delete("/library/authors/{$author->id}");

    $response->assertRedirect('/library/authors');
    $response->assertSessionHas('success', 'Penulis berhasil dihapus.');
    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

test('15. BookNotAvailableException and AuthorHasBooksException render JSON correctly on API request', function () {
    $bookEx = new BookNotAvailableException('Buku Habis', 99);
    $authorEx = new AuthorHasBooksException('Penulis Sibuk', 44, 3);

    $req = \Illuminate\Http\Request::create('/api/test', 'POST');
    $req->headers->set('Accept', 'application/json');

    $res1 = $bookEx->render($req);
    expect($res1->status())->toBe(422);
    $data1 = json_decode($res1->getContent(), true);
    expect($data1['error'])->toBe('book_not_available');
    expect($data1['details']['book_title'])->toBe('Buku Habis');

    $res2 = $authorEx->render($req);
    expect($res2->status())->toBe(422);
    $data2 = json_decode($res2->getContent(), true);
    expect($data2['error'])->toBe('author_has_books');
    expect($data2['details']['book_count'])->toBe(3);
});
