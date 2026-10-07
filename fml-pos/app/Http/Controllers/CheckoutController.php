<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
  public function process(Request $request)
{
    $request->validate([
        'payment_method' => 'required|in:cash,card,gcash,maya,bank_transfer',
        'amount_paid' => 'required|numeric|min:0',
    ]);

    $cart = session('cart', []);

    if (empty($cart)) {
        return redirect()->route('dashboard')
            ->with('status', 'Your cart is empty.');
    }

    try {
        $receipt = DB::transaction(function () use ($request, $cart): array {
            $total = 0;
            $items = [];
            $products = [];

            foreach ($cart as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if (!$product) {
                    throw new \Exception(
                        "Product {$item['name']} no longer exists."
                    );
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception(
                        "Not enough stock for {$product->name}."
                    );
                }

                $itemTotal = $product->price * $item['quantity'];
                $total += $itemTotal;

                $items[] = [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];
                $products[] = $product;

                $product->decrement('stock', $item['quantity']);

                if ($product->stock <= 0) {
                    $product->update([
                        'is_available' => false
                    ]);
                }
            }

            $paymentMethod = $request->payment_method;
            $amountPaid = (float) $request->amount_paid;

            if ($amountPaid < $total) {
                throw new \Exception(
                    'Insufficient payment. Please enter an amount equal to or greater than the total.'
                );
            }

            $change = $amountPaid - $total;
            $completedAt = now();
            $receiptNumber = 'REC-'.$completedAt->format('YmdHis').'-'.Str::upper(Str::random(6));

            $transaction = Transaction::create([
                'receipt_number' => $receiptNumber,
                'user_id' => $request->user()->id,
                'payment_method' => $paymentMethod,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change' => $change,
                'completed_at' => $completedAt,
            ]);

            foreach ($items as $index => $item) {
                $transaction->items()->create([
                    'product_id' => $products[$index]->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);
            }

            return [
                'receipt_number' => $receiptNumber,
                'date' => $completedAt->format('F d, Y h:i A'),
                'items' => $items,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change' => $change,
            ];
        });

        session([
            'last_receipt' => $receipt
        ]);

        session()->forget('cart');

        return redirect()->route('checkout.receipt', [
            'receipt' => $receipt['receipt_number']
        ]);

    } catch (\Throwable $e) {
        return redirect()->route('dashboard')
            ->with('status', $e->getMessage());
    }
}

    public function receipt($receipt)
    {
        $data = session('last_receipt');

        if (!$data || $data['receipt_number'] !== $receipt) {
            return redirect()->route('dashboard')
                ->with('status', 'Receipt not found.');
        }

        return view('receipt', compact('data'));
    }


    public function pdf($receipt)
    {
        $data = session('last_receipt');

        if (!$data || $data['receipt_number'] !== $receipt) {
            return redirect()->route('dashboard')
                ->with('status', 'Receipt not found.');
        }

        $pdf = Pdf::loadView('receipt-pdf', [
            'data' => $data
        ]);

        return $pdf->stream(
            $data['receipt_number'] . '.pdf'
        );
    }
}