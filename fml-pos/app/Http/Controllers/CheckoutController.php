<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('dashboard')
                ->with('status', 'Your cart is empty.');
        }

        DB::beginTransaction();

        try {
            $total = 0;
            $items = [];

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

                // Calculate item total
                $itemTotal = $product->price * $item['quantity'];
                $total += $itemTotal;

                $items[] = [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];

                // DECREASE STOCK
                $product->decrement('stock', $item['quantity']);

                // Automatically unavailable when stock reaches 0
                if ($product->stock <= 0) {
                    $product->update([
                        'is_available' => false
                    ]);
                }
            }

            DB::commit();

            // Create receipt data
            $receipt = [
                'receipt_number' => 'REC-' . strtoupper(
                    now()->format('YmdHis')
                ),
                'date' => now()->format('F d, Y h:i A'),
                'items' => $items,
                'total' => $total,
            ];

            // Save receipt temporarily in session
            session([
                'last_receipt' => $receipt
            ]);

            // Clear cart AFTER successful checkout
            session()->forget('cart');

            return redirect()->route('checkout.receipt', [
                'receipt' => $receipt['receipt_number']
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

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