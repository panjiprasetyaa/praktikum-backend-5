<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Penulis Perpustakaan') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('library.books.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    📚 Lihat Buku
                </a>
                <a href="{{ route('library.authors.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    + Tambah Penulis
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

            {{-- Flash Message Error (dari Exception Handler) --}}
            @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline font-semibold">⚠️ {{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">No</th>
                                <th class="py-3 px-6 text-left">Nama Penulis</th>
                                <th class="py-3 px-6 text-left">Email</th>
                                <th class="py-3 px-6 text-center">Jumlah Buku</th>
                                <th class="py-3 px-6 text-left">Bio</th>
                                <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @forelse ($authors as $index => $author)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $authors->firstItem() + $index }}</td>
                                <td class="py-3 px-6 text-left font-semibold text-gray-800">{{ $author->nama }}</td>
                                <td class="py-3 px-6 text-left">{{ $author->email }}</td>
                                <td class="py-3 px-6 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $author->books_count }} buku
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-left">{{ Str::limit($author->bio, 50, '...') ?: '-' }}</td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('library.authors.edit', $author->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</a>
                                        <form action="{{ route('library.authors.destroy', $author->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penulis ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold ml-2">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-4 px-6 text-center text-gray-500">
                                    Belum ada data penulis. Silakan tambah data penulis terlebih dahulu.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $authors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
