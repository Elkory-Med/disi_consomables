<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\ShoppingCartComponent;

class ShoppingCartTest extends TestCase
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

    public function test_can_add_item_to_cart()
    {
        $this->actingAs($this->user);
        
        Livewire::test(ShoppingCartComponent::class)
            ->call('addToCart', $this->product->id);

        $this->assertDatabaseHas('shopping_cart', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
    }

    public function test_cannot_add_to_cart_when_pending()
    {
        $pendingUser = User::factory()->create([
            'status' => 'pending',
            'role' => 0
        ]);
        
        $this->actingAs($pendingUser);
        
        Livewire::test(ShoppingCartComponent::class)
            ->call('addToCart', $this->product->id)
            ->assertSet('cartItems', [])
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('shopping_cart', [
            'user_id' => $pendingUser->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_can_update_cart_quantity()
    {
        $this->actingAs($this->user);
        
        $cartItem = ShoppingCart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        Livewire::test(ShoppingCartComponent::class)
            ->call('updateQuantity', $cartItem->id, 2);

        $this->assertDatabaseHas('shopping_cart', [
            'id' => $cartItem->id,
            'quantity' => 2
        ]);
    }

    public function test_can_remove_item_from_cart()
    {
        $this->actingAs($this->user);
        
        $cartItem = ShoppingCart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        Livewire::test(ShoppingCartComponent::class)
            ->call('removeItem', $cartItem->id);

        $this->assertDatabaseMissing('shopping_cart', [
            'id' => $cartItem->id
        ]);
    }

    public function test_cart_requires_authentication()
    {
        Livewire::test(ShoppingCartComponent::class)
            ->assertRedirect(route('login'));
    }

    public function test_can_create_order()
    {
        $this->actingAs($this->user);
        
        $cartItem = ShoppingCart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        Livewire::test(ShoppingCartComponent::class)
            ->call('createOrder');

        $this->assertDatabaseMissing('shopping_cart', [
            'id' => $cartItem->id
        ]);
    }
}
