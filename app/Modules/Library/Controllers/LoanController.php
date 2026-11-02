<?php

namespace Modules\Library\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Library\Exceptions\BookNotAvailableException;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookLoan;

class LoanController extends Controller
{
    /**
     * Memproses peminjaman buku dengan validasi stok sebelum transaksi.
     */
    public function borrow(Request $request, Book $book)
    {
        // Fitur Tambahan: Validasi ketersediaan stok sebelum peminjaman
        if ($book->stok <= 0) {
            throw new BookNotAvailableException($book->judul, $book->id);
        }

        // Kurangi stok buku
        $book->decrement('stok', 1);

        // Catat transaksi peminjaman
        $loan = BookLoan::create([
            'book_id' => $book->id,
            'user_id' => auth()->id() ?? 1,
            'borrowed_at' => now(),
            'status' => 'borrowed',
        ]);

        return redirect()
            ->route('library.books.index')
            ->with('success', "Buku '{$book->judul}' berhasil dipinjam.");
    }

    /**
     * Mengembalikan buku yang dipinjam.
     */
    public function returnBook(BookLoan $loan)
    {
        if ($loan->status === 'returned') {
            return back()->with('error', 'Buku ini sudah dikembalikan sebelumnya.');
        }

        $loan->update([
            'returned_at' => now(),
            'status' => 'returned',
        ]);

        $loan->book->increment('stok', 1);

        return redirect()
            ->route('library.books.index')
            ->with('success', "Buku '{$loan->book->judul}' berhasil dikembalikan.");
    }
}
