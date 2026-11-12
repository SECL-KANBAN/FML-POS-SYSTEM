<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        if (! $product->is_available) {
            return $this->addResponse($request, $product, 'This product is currently unavailable.', 422);
        }

        if ($product->stock <= 0) {
            return $this->addResponse($request, $product, 'This product is out of stock.', 422);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['quantity'] >= $product->stock) {
                return $this->addResponse($request, $product, 'Not enough stock available.', 422);
            }

            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'image' => $product->image_path,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);

        return $this->addResponse($request, $product, $product->name . ' added to cart.');
    }

    public function scan(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::query()
            ->where('sku', $data['barcode'])
            ->first();

        if (! $product && preg_match('/^PID-(\d+)$/i', $data['barcode'], $matches)) {
            $product = Product::query()->find($matches[1]);
        }

        if (! $product) {
            return back()->with('status', 'No product found for barcode '.$data['barcode'].'.');
        }

        return $this->add($request, $product);
    }

    private function addResponse(
        Request $request,
        Product $product,
        string $message,
        int $statusCode = 200
    ): JsonResponse|RedirectResponse {
        if (! $request->expectsJson()) {
            return back()->with('status', $message);
        }

        $cart = session()->get('cart', []);
        $totalItems = array_sum(array_column($cart, 'quantity'));
        $subtotal = array_reduce(
            $cart,
            fn (float $total, array $item): float => $total + ((float) $item['price'] * $item['quantity']),
            0.0
        );

        return response()->json([
            'message' => $message,
            'product_id' => $product->id,
            'quantity' => $cart[$product->id]['quantity'] ?? 0,
            'cart' => array_values($cart),
            'total_items' => $totalItems,
            'subtotal' => $subtotal,
        ], $statusCode);
    }

    public function decrease(Product $product)
    {
        $cart = session()->get('cart', []);

        if (! isset($cart[$product->id])) {
            return back();
        }

        if ($cart[$product->id]['quantity'] > 1) {
            $cart[$product->id]['quantity']--;
        } else {
            unset($cart[$product->id]);
        }

        session()->put('cart', $cart);

        return back();
    }

    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);

        unset($cart[$product->id]);

        session()->put('cart', $cart);

        return back();
    }
}