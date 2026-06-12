<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:price_asc,price_desc'],
        ]);

        return Inertia::render('catalog/index', [
            'books' => Book::query()
                ->published()
                ->search($filters['q'] ?? null)
                ->inCategory($filters['category'] ?? null)
                ->sorted($filters['sort'] ?? null)
                ->with('category:id,name,slug')
                ->paginate(12)
                ->through(fn (Book $book) => $this->bookCard($book))
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'sort' => $filters['sort'] ?? '',
            ],
        ]);
    }

    public function show(Request $request, Book $book): Response
    {
        abort_unless($book->status === 'published', 404);

        $user = $request->user();

        return Inertia::render('catalog/show', [
            'book' => [
                ...$this->bookCard($book->load('category:id,name,slug')),
                'description' => $book->description,
                'file_format' => $book->file_format,
            ],
            'owned' => $user !== null && $user->ownsBook($book),
        ]);
    }

    /**
     * Public-safe book payload. Never includes file_path.
     *
     * @return array<string, mixed>
     */
    private function bookCard(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'slug' => $book->slug,
            'price' => $book->price,
            'accent_color' => $book->accent_color,
            'cover_url' => $book->cover_path ? Storage::disk('public')->url($book->cover_path) : null,
            'category' => $book->category ? ['name' => $book->category->name, 'slug' => $book->category->slug] : null,
        ];
    }
}
