<?php

namespace Modules\Library\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class AuthorHasBooksException extends Exception
{
    protected string $authorName;

    protected int $authorId;

    protected int $bookCount;

    public function __construct(string $authorName, int $authorId, int $bookCount)
    {
        $this->authorName = $authorName;
        $this->authorId = $authorId;
        $this->bookCount = $bookCount;

        $message = "Penulis '{$authorName}' tidak dapat dihapus karena masih memiliki {$bookCount} buku yang terdaftar.";
        parent::__construct($message);

        // Nilai Plus: Logging error ke storage/logs/laravel.log
        Log::error("[Perpustakaan] Penghapusan penulis gagal: {$message} (Author ID: {$authorId})");
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getBookCount(): int
    {
        return $this->bookCount;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'author_has_books',
                'details' => [
                    'author_id' => $this->authorId,
                    'author_name' => $this->authorName,
                    'book_count' => $this->bookCount,
                ],
            ], 422);
        }

        return back()->withErrors([
            'author' => $this->getMessage(),
        ])->with('error', $this->getMessage());
    }
}
