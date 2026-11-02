<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Buku Perpustakaan') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('library.authors.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    ✍️ Kelola Penulis
                </a>
                <a href="{{ route('library.books.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    + Tambah Buku
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Message Sukses --}}
            @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline font-medium">{{ session('success') }}</span>
            </div>
            @endif

            {{-- Flash Message Error / Exception --}}
            @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline font-semibold">⚠️ {{ session('error') }}</span>
            </div>
            @endif

            @if ($errors->has('book_id'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline font-semibold">⚠️ {{ $errors->first('book_id') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">No</th>
                                <th class="py-3 px-6 text-left">Judul Buku</th>
                                <th class="py-3 px-6 text-left">ISBN</th>
                                <th class="py-3 px-6 text-left">Penulis</th>
                                <th class="py-3 px-6 text-center">Tahun</th>
                                <th class="py-3 px-6 text-right">Harga</th>
                                <th class="py-3 px-6 text-center">Stok</th>
                                <th class="py-3 px-6 text-center">Aksi Peminjaman</th>
                                <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @forelse ($books as $index => $book)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $books->firstItem() + $index }}</td>
                                <td class="py-3 px-6 text-left font-semibold text-gray-800">{{ $book->judul }}</td>
                                <td class="py-3 px-6 text-left font-mono text-xs">{{ $book->isbn }}</td>
                                <td class="py-3 px-6 text-left text-indigo-700 font-medium">{{ $book->author?->nama ?? '-' }}</td>
                                <td class="py-3 px-6 text-center">{{ $book->tahun_terbit }}</td>
                                <td class="py-3 px-6 text-right">Rp {{ number_format($book->harga, 2, ',', '.') }}</td>
                                <td class="py-3 px-6 text-center">
                                    @if ($book->stok > 0)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $book->stok }} eks
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                        0 (Habis)
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-6 text-center">
                                    {{-- Form Tombol Pinjam Buku --}}
                                    <form action="{{ route('library.books.borrow', $book->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            class="py-1 px-3 text-xs font-semibold rounded shadow-sm text-white {{ $book->stok > 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700' }}"
                                            title="{{ $book->stok == 0 ? 'Klik untuk menguji BookNotAvailableException (Stok 0)' : 'Pinjam 1 eksemplar buku ini' }}">
                                            📖 Pinjam Buku
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('library.books.edit', $book->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</a>
                                        <form action="{{ route('library.books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold ml-2">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-4 px-6 text-center text-gray-500">
                                    Belum ada data buku. Silakan klik '+ Tambah Buku' untuk memasukkan buku baru.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
