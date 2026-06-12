<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_the_catalog_and_only_see_published_books()
    {
        Book::factory()->published()->create(['title' => 'Visible Book']);
        Book::factory()->create(['title' => 'Hidden Draft', 'status' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('catalog/index')
                ->has('books.data', 1)
                ->where('books.data.0.title', 'Visible Book'));
    }

    public function test_search_matches_title_and_author_case_insensitively()
    {
        Book::factory()->published()->create(['title' => 'Dragons of Autumn']);
        Book::factory()->published()->create(['author' => 'Dragomir Petrov', 'title' => 'Some Other Tale']);
        Book::factory()->published()->create(['title' => 'Cozy Mystery']);

        $this->get('/?q=DRAGO')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('books.data', 2));
    }

    public function test_category_filter_and_price_sort()
    {
        $fantasy = Category::factory()->create(['name' => 'Fantasy', 'slug' => 'fantasy']);
        Book::factory()->published()->create(['category_id' => $fantasy->id, 'price' => 19.99]);
        Book::factory()->published()->create(['category_id' => $fantasy->id, 'price' => 4.99]);
        Book::factory()->published()->create(['price' => 9.99]);

        $this->get('/?category=fantasy&sort=price_asc')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('books.data', 2)
                ->where('books.data.0.price', '4.99'));
    }

    public function test_book_detail_page_renders_without_exposing_file_path()
    {
        $book = Book::factory()->published()->create();

        $this->get("/books/{$book->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('catalog/show')
                ->where('book.title', $book->title)
                ->where('owned', false)
                ->missing('book.file_path'));
    }

    public function test_draft_books_are_not_visible_on_detail_page()
    {
        $book = Book::factory()->create(['status' => 'draft']);

        $this->get("/books/{$book->slug}")->assertNotFound();
    }

    public function test_owner_sees_owned_flag_on_detail_page()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();
        $user->books()->attach($book->id);

        $this->actingAs($user)
            ->get("/books/{$book->slug}")
            ->assertInertia(fn (AssertableInertia $page) => $page->where('owned', true));
    }
}
