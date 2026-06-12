<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_get_403_on_every_admin_route()
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $book = Book::factory()->create();

        $routes = [
            ['get', '/admin'],
            ['get', '/admin/books'],
            ['get', '/admin/books/create'],
            ['post', '/admin/books'],
            ['get', "/admin/books/{$book->id}/edit"],
            ['put', "/admin/books/{$book->id}"],
            ['delete', "/admin/books/{$book->id}"],
            ['get', '/admin/orders'],
        ];

        foreach ($routes as [$method, $uri]) {
            $this->actingAs($customer)->{$method}($uri)->assertForbidden();
        }
    }

    public function test_guests_are_redirected_to_login_on_admin_routes()
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/books')->assertRedirect('/login');
        $this->get('/admin/orders')->assertRedirect('/login');
    }

    public function test_admins_can_access_the_admin_panel()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/books')->assertOk();
        $this->actingAs($admin)->get('/admin/books/create')->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
    }
}
