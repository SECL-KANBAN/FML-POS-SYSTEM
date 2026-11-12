<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated user can add a product with an automatically generated sku and picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('products.store'), [
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 12,
        'is_available' => 1,
        'picture' => UploadedFile::fake()->createWithContent(
            'coffee.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        ),
    ]);

    $product = Product::query()->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    expect($product->sku)->toBe('SKU-000001')
        ->and($product->name)->toBe('Coffee')
        ->and($product->price)->toBe('4.50')
        ->and($product->stock)->toBe(12)
        ->and($product->is_available)->toBeTrue();
    Storage::disk('public')->assertExists($product->image_path);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Coffee')
        ->assertSee('SKU-000001')
        ->assertSee('Add to cart');
});

test('authenticated user can edit and delete a product', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $imagePath = UploadedFile::fake()->createWithContent(
        'coffee.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
    )->store('products', 'public');
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 12,
        'is_available' => true,
        'image_path' => $imagePath,
    ]);
    $product->sku = 'SKU-000001';
    $product->save();

    $this->actingAs($user)
        ->put(route('products.update', $product), [
            'name' => 'Iced Coffee',
            'price' => '5.25',
            'stock' => 8,
            'is_available' => 0,
        ])
        ->assertRedirect(route('dashboard'));

    expect($product->refresh()->name)->toBe('Iced Coffee')
        ->and($product->sku)->toBe('SKU-000001')
        ->and($product->is_available)->toBeFalse();

    $this->delete(route('products.destroy', $product))
        ->assertRedirect(route('dashboard'));

    expect(Product::query()->find($product->id))->toBeNull();
    Storage::disk('public')->assertMissing($imagePath);
});

test('product management requires authentication', function () {
    $this->post(route('products.store'), [])
        ->assertRedirect(route('login'));
});
