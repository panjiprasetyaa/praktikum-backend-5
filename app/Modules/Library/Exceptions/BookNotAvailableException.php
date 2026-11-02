<?php

namespace Modules\Library\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class BookNotAvailableException extends Exception
{
    protected string $bookTitle;

    protected int $bookId;

    public function __construct(string $bookTitle, int $bookId)
    {
        $this->bookTitle = $bookTitle;
        $this->bookId = $bookId;

        $message = "Buku '{$bookTitle}' tidak tersedia untuk dipinjam karena stok habis (0).";
        parent::__construct($message);

        // Nilai Plus: Logging error ke storage/logs/laravel.log
        Log::error("[Perpustakaan] Peminjaman gagal: {$message} (Book ID: {$bookId})");
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'book_not_available',
                'details' => [
                    'book_id' => $this->bookId,
                    'book_title' => $this->bookTitle,
                    'stock' => 0,
                ],
            ], 422);
        }

        return back()->withErrors([
            'book_id' => $this->getMessage(),
        ])->with('error', $this->getMessage());
    }
}
