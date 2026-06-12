<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookRequest;
use App\Http\Requests\Admin\UpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/books/index', [
            'books' => Book::with('category')
                ->latest()
                ->paginate(15)
                ->through(fn (Book $book) => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'price' => $book->price,
                    'status' => $book->status,
                    'category' => $book->category?->name,
                ])
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/books/create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['file_path'] = $request->file('file')->store('ebooks', 'private');
        $data['file_format'] = strtolower($request->file('file')->getClientOriginalExtension());

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $data['slug'] = $this->uniqueSlug($data['title']);

        unset($data['file'], $data['cover']);

        Book::create($data);

        return redirect()->route('admin.books.index')->with('success', 'Book created.');
    }

    public function edit(Book $book): Response
    {
        return Inertia::render('admin/books/edit', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'description' => $book->description,
                'price' => $book->price,
                'category_id' => $book->category_id,
                'accent_color' => $book->accent_color,
                'status' => $book->status,
                'file_format' => $book->file_format,
                'cover_url' => $book->cover_path ? Storage::disk('public')->url($book->cover_path) : null,
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            Storage::disk('private')->delete($book->file_path);
            $data['file_path'] = $request->file('file')->store('ebooks', 'private');
            $data['file_format'] = strtolower($request->file('file')->getClientOriginalExtension());
        }

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($data['title'] !== $book->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $book->id);
        }

        unset($data['file'], $data['cover']);

        $book->update($data);

        return redirect()->route('admin.books.index')->with('success', 'Book updated.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        Storage::disk('private')->delete($book->file_path);

        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Book deleted.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Book::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
