<?php

use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageStorage;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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

test('authenticated user can upload and remove a product image in Vercel Blob', function () {
    config([
        'product_images.driver' => 'vercel_blob',
        'product_images.vercel_blob.token' => 'vercel_blob_rw_store123_secret',
    ]);

    $blobUrl = 'https://store123.public.blob.vercel-storage.com/products/coffee.png';
    $pictureContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

    Http::fake(function (HttpRequest $request) use ($blobUrl) {
        if ($request->method() === 'PUT') {
            return Http::response(['url' => $blobUrl], 200);
        }

        return Http::response([], 200);
    });

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('products.store'), [
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 12,
        'is_available' => 1,
        'picture' => UploadedFile::fake()->createWithContent('coffee.png', $pictureContents),
    ])->assertRedirect(route('dashboard'));

    $product = Product::query()->firstOrFail();

    expect($product->image_path)->toBe($blobUrl)
        ->and($product->image_url)->toBe($blobUrl);

    Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'PUT'
        && str_starts_with($request->url(), 'https://vercel.com/api/blob/?pathname=products%2F')
        && $request->hasHeader('x-vercel-blob-store-id', 'store123')
        && $request->hasHeader('x-vercel-blob-access', 'public')
        && $request->body() === $pictureContents);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee($blobUrl);

    $this->actingAs($user)->delete(route('products.destroy', $product))
        ->assertRedirect(route('dashboard'));

    Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://vercel.com/api/blob/delete'
        && $request->data()['urls'] === [$blobUrl]);
});

test('vercel blob storage fails explicitly when its token is missing', function () {
    Storage::fake('public');
    config([
        'product_images.driver' => 'vercel_blob',
        'product_images.vercel_blob.token' => null,
    ]);

    expect(fn () => app(ProductImageStorage::class)->store(
        UploadedFile::fake()->createWithContent(
            'coffee.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        )
    ))->toThrow(RuntimeException::class, 'BLOB_READ_WRITE_TOKEN is required');

    expect(Storage::disk('public')->allFiles('products'))->toBe([]);
});

test('product management requires authentication', function () {
    $this->post(route('products.store'), [])
        ->assertRedirect(route('login'));
});
