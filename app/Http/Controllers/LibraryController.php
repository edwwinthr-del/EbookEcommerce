<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('library/index', [
            'books' => $request->user()
                ->books()
                ->with('category:id,name,slug')
                ->orderByPivot('created_at', 'desc')
                ->get()
                ->map(fn (Book $book) => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'slug' => $book->slug,
                    'accent_color' => $book->accent_color,
                    'cover_url' => $book->cover_path ? Storage::disk('public')->url($book->cover_path) : null,
                    'file_format' => $book->file_format,
                    'category' => $book->category ? ['name' => $book->category->name, 'slug' => $book->category->slug] : null,
                ]),
        ]);
    }

    public function download(Request $request, Book $book): StreamedResponse
    {
        abort_unless($request->user()->ownsBook($book), 403);

        return Storage::disk('private')->download(
            $book->file_path,
            $book->title.'.'.$book->file_format,
        );
    }
}
