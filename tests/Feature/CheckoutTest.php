<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function fakeStripeSessions(): void
    {
        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('createSession')
                ->andReturn((object) [
                    'id' => 'cs_test_fake_session',
                    'url' => 'https://checkout.stripe.com/c/pay/cs_test_fake_session',
                ]);
        });
    }

    public function test_checkout_uses_database_price_even_if_request_tampers_with_price()
    {
        $this->fakeStripeSessions();

        $user = User::factory()->create();
        $book = Book::factory()->published()->create(['price' => 19.99]);

        $this->actingAs($user)->post("/checkout/{$book->id}", [
            'price' => '0.01',
            'total' => '0.01',
        ]);

        $order = Order::sole();

        $this->assertSame('19.99', $order->total);
        $this->assertSame('19.99', $order->items->sole()->price);
        $this->assertSame('pending', $order->status);
        $this->assertSame('cs_test_fake_session', $order->stripe_session_id);
    }

    public function test_checkout_is_forbidden_when_user_already_owns_the_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();
        $user->books()->attach($book->id);

        $this->actingAs($user)->post("/checkout/{$book->id}")->assertForbidden();
    }

    public function test_checkout_requires_login()
    {
        $book = Book::factory()->published()->create();

        $this->post("/checkout/{$book->id}")->assertRedirect('/login');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_webhook_grants_library_access()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create(['price' => 9.99]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 9.99,
            'stripe_session_id' => 'cs_test_webhook',
        ]);
        $order->items()->create(['book_id' => $book->id, 'price' => 9.99]);

        event(new WebhookReceived([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_webhook']],
        ]));

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertTrue($user->fresh()->ownsBook($book));
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_webhook_ignores_unrelated_event_types()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_other',
        ]);
        $order->items()->create(['book_id' => $book->id, 'price' => $book->price]);

        event(new WebhookReceived([
            'type' => 'payment_intent.created',
            'data' => ['object' => ['id' => 'cs_test_other']],
        ]));

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertFalse($user->fresh()->ownsBook($book));
    }

    public function test_success_page_alone_does_not_grant_access()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_success_page',
        ]);
        $order->items()->create(['book_id' => $book->id, 'price' => $book->price]);

        $this->actingAs($user)
            ->get('/checkout/success?session_id=cs_test_success_page')
            ->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertFalse($user->fresh()->ownsBook($book));
    }

    public function test_non_owner_gets_403_on_download()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();

        $this->actingAs($user)->get("/library/{$book->id}/download")->assertForbidden();
    }

    public function test_owner_can_download_their_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->published()->create();

        \Illuminate\Support\Facades\Storage::disk('private')->put($book->file_path, '%PDF-1.4 fake');
        $user->books()->attach($book->id);

        $this->actingAs($user)
            ->get("/library/{$book->id}/download")
            ->assertOk()
            ->assertDownload($book->title.'.'.$book->file_format);
    }
}
