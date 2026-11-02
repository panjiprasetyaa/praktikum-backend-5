<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'isbn',
        'tahun_terbit',
        'harga',
        'stok',
        'author_id',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'tahun_terbit' => 'integer',
        'stok' => 'integer',
    ];

    /**
     * Relasi ke penulis buku.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Relasi ke transaksi peminjaman buku.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class, 'book_id');
    }
}
