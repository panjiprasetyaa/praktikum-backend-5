<?php

namespace Modules\Library\Controllers;

use App\Http\Controllers\Controller;
use Modules\Library\Exceptions\AuthorHasBooksException;
use Modules\Library\Models\Author;
use Modules\Library\Requests\StoreAuthorRequest;
use Modules\Library\Requests\UpdateAuthorRequest;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('books')->paginate(10);

        return view('library.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('library.authors.create');
    }

    public function store(StoreAuthorRequest $request)
    {
        Author::create($request->validated());

        return redirect()
            ->route('library.authors.index')
            ->with('success', 'Penulis berhasil ditambahkan.');
    }

    public function edit(Author $author)
    {
        return view('library.authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author)
    {
        $author->update($request->validated());

        return redirect()
            ->route('library.authors.index')
            ->with('success', 'Data penulis berhasil diperbarui.');
    }

    public function destroy(Author $author)
    {
        $bookCount = $author->books()->count();

        // Pemeriksaan integritas data: cegah penghapusan jika penulis masih memiliki buku
        if ($bookCount > 0) {
            throw new AuthorHasBooksException($author->nama, $author->id, $bookCount);
        }

        $author->delete();

        return redirect()
            ->route('library.authors.index')
            ->with('success', 'Penulis berhasil dihapus.');
    }
}
