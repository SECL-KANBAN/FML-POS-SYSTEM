<?php

use App\Models\Product;
use App\Models\User;

test('scanning a product sku adds that product to the cart', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 5,
        'is_available' => true,
    ]);
    $product->sku = 'SKU-000001';
    $product->save();

    $this->actingAs($user)
        ->post(route('cart.scan'), ['barcode' => 'SKU-000001'])
        ->assertRedirect()
        ->assertSessionHas('status', 'Coffee added to cart.');

    expect(session('cart.'.$product->id))
        ->toMatchArray([
            'id' => $product->id,
            'name' => 'Coffee',
            'sku' => 'SKU-000001',
            'quantity' => 1,
        ]);
});

test('cart quantity updates return fresh cart totals without a redirect', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 5,
        'is_available' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => null,
                    'price' => '4.50',
                    'image' => null,
                    'quantity' => 1,
                ],
            ],
        ])
        ->postJson(route('cart.add', $product));

    $response->assertOk()
        ->assertJsonPath('product_id', $product->id)
        ->assertJsonPath('quantity', 2)
        ->assertJsonPath('total_items', 2)
        ->assertJsonPath('subtotal', 9);

    expect(session('cart.'.$product->id.'.quantity'))->toBe(2);
});

test('scanning the fallback product barcode adds products without a sku', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Tea',
        'price' => '3.00',
        'stock' => 5,
        'is_available' => true,
    ]);

    $this->actingAs($user)
        ->post(route('cart.scan'), ['barcode' => 'PID-'.$product->id])
        ->assertRedirect()
        ->assertSessionHas('status', 'Tea added to cart.');

    expect(session('cart.'.$product->id.'.quantity'))->toBe(1);
});

test('unknown barcodes and unavailable products are not added to cart', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Unavailable Tea',
        'price' => '3.00',
        'stock' => 5,
        'is_available' => false,
    ]);
    $product->sku = 'SKU-000002';
    $product->save();

    $this->actingAs($user)
        ->post(route('cart.scan'), ['barcode' => 'UNKNOWN'])
        ->assertRedirect()
        ->assertSessionHas('status', 'No product found for barcode UNKNOWN.');

    $this->post(route('cart.scan'), ['barcode' => 'SKU-000002'])
        ->assertRedirect()
        ->assertSessionHas('status', 'This product is currently unavailable.');

    expect(session('cart', []))->toBe([]);
});

test('dashboard renders generated product barcodes and camera scanner controls', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 5,
        'is_available' => true,
    ]);
    $product->sku = 'SKU-000001';
    $product->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Product Data Matrix')
        ->assertSee('Data Matrix code for')
        ->assertSee('Scan with camera')
        ->assertSee('Scan barcode or enter SKU');
});
