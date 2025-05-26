<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'image' => 'test.jpg'
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'image' => 'test.jpg'
        ]);
    }

    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => $category->id
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_has_required_fields()
    {
        $product = new Product();
        $this->assertFalse($product->save());

        $this->assertArrayHasKey('name', $product->getErrors());
        $this->assertArrayHasKey('category_id', $product->getErrors());
    }
}
