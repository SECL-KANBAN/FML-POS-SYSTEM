<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

test('successful checkout is saved and shown in transaction history', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 5,
        'is_available' => true,
    ]);
    $product->sku = 'SKU-000001';
    $product->save();

    $response = $this->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2,
                ],
            ],
        ])
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'amount_paid' => '10.00',
        ]);

    $transaction = Transaction::query()->with('items')->sole();

    $response->assertRedirect(route('checkout.receipt', $transaction->receipt_number));
    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->payment_method)->toBe('cash')
        ->and($transaction->total)->toBe('9.00')
        ->and($transaction->amount_paid)->toBe('10.00')
        ->and($transaction->change)->toBe('1.00')
        ->and($transaction->items)->toHaveCount(1)
        ->and($transaction->items[0]->name)->toBe('Coffee')
        ->and($transaction->items[0]->sku)->toBe('SKU-000001')
        ->and($transaction->items[0]->price)->toBe('4.50')
        ->and($transaction->items[0]->quantity)->toBe(2)
        ->and($transaction->items[0]->total)->toBe('9.00')
        ->and($product->refresh()->stock)->toBe(3);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertSee($transaction->receipt_number)
        ->assertSee('Coffee')
        ->assertSee('SKU-000001')
        ->assertSee('Cash')
        ->assertSee('₱9.00')
        ->assertSee('₱10.00')
        ->assertSee('₱1.00')
        ->assertSee('Completed');
});

test('failed checkout does not create transaction history or reduce stock', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Coffee',
        'price' => '4.50',
        'stock' => 5,
        'is_available' => true,
    ]);

    $this->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2,
                ],
            ],
        ])
        ->post(route('checkout.process'), [
            'payment_method' => 'cash',
            'amount_paid' => '5.00',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('cart');

    expect(Transaction::query()->count())->toBe(0)
        ->and($product->refresh()->stock)->toBe(5);
});
