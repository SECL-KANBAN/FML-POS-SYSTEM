<?php

use App\Models\Transaction;
use App\Models\User;

test('authenticated user can view analytics based on completed transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::query()->create([
        'receipt_number' => 'REC-ANALYTICS-001',
        'user_id' => $user->id,
        'payment_method' => 'gcash',
        'total' => '500.00',
        'amount_paid' => '500.00',
        'change' => '0.00',
        'completed_at' => now(),
    ]);
    $transaction->items()->create([
        'name' => 'Coffee',
        'sku' => 'SKU-000001',
        'price' => '250.00',
        'quantity' => 2,
        'total' => '500.00',
    ]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertSee('Sales overview')
        ->assertSee('₱500.00')
        ->assertSee('Completed transactions')
        ->assertSee('Units sold')
        ->assertSee('Daily revenue')
        ->assertSee('GCash')
        ->assertSee('Coffee')
        ->assertSee('SKU-000001');
});

test('analytics page displays empty states when there are no completed transactions', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertSee('₱0.00')
        ->assertSee('No payment data yet.')
        ->assertSee('No product sales yet.');
});

test('analytics requires authentication', function () {
    $this->get(route('analytics.index'))
        ->assertRedirect(route('login'));
});
