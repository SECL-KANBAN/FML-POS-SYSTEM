<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Product $product)
    {
        if (! $product->is_available) {
            return back()->with('status', 'This product is currently unavailable.');
        }

        if ($product->stock <= 0) {
            return back()->with('status', 'This product is out of stock.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['quantity'] >= $product->stock) {
                return back()->with('status', 'Not enough stock available.');
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

        return back()->with('status', $product->name . ' added to cart.');
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