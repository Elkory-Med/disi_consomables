<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\ShoppingCartComponent;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'status' => 'approved',
            'role' => 0 // 0 for regular user
        ]);
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);
    }

    public function test_can_create_order()
    {
        $this->actingAs($this->user);
        
        $cartItem = ShoppingCart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        Livewire::test(ShoppingCartComponent::class)
            ->call('createCheckoutSession')
            ->assertRedirect('/user/orders');

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    public function test_cannot_create_order_with_empty_cart()
    {
        $this->actingAs($this->user);
        
        Livewire::test(ShoppingCartComponent::class)
            ->call('createCheckoutSession');

        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id
        ]);
    }

    public function test_cannot_create_order_when_pending()
    {
        $pendingUser = User::factory()->create([
            'status' => 'pending',
            'role' => 0
        ]);
        
        $this->actingAs($pendingUser);
        
        ShoppingCart::create([
            'user_id' => $pendingUser->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        Livewire::test(ShoppingCartComponent::class)
            ->call('createCheckoutSession');

        $this->assertDatabaseMissing('orders', [
            'user_id' => $pendingUser->id
        ]);
    }
}
