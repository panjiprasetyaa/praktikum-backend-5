<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Controllers\AuthorController;
use Modules\Library\Controllers\BookController;
use Modules\Library\Controllers\LoanController;

// Rute modul Perpustakaan terproteksi autentikasi dan sesi web
Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function () {
    Route::resource('authors', AuthorController::class);
    Route::resource('books', BookController::class);
    Route::post('books/{book}/borrow', [LoanController::class, 'borrow'])->name('books.borrow');
    Route::post('loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
});
