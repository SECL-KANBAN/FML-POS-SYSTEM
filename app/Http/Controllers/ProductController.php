<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_available' => ['required', 'boolean'],
            'picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = $this->storePicture($request);

        try {
            DB::transaction(function () use ($data, $imagePath): void {
                $product = Product::create([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'is_available' => $data['is_available'],
                    'image_path' => $imagePath,
                ]);

                $product->sku = 'SKU-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
                $product->save();
            });
        } catch (Throwable $exception) {
            $this->deletePicture($imagePath);
            throw $exception;
        }

        return redirect()->route('dashboard')->with('status', 'Product added successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_available' => ['required', 'boolean'],
            'picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $newImagePath = $this->storePicture($request);
        $oldImagePath = $product->image_path;

        try {
            $product->update([
                'name' => $data['name'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'is_available' => $data['is_available'],
                'image_path' => $newImagePath ?? $oldImagePath,
            ]);
        } catch (Throwable $exception) {
            $this->deletePicture($newImagePath);
            throw $exception;
        }

        if ($newImagePath !== null) {
            $this->deletePicture($oldImagePath);
        }

        return redirect()->route('dashboard')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePath = $product->image_path;
        $product->delete();
        $this->deletePicture($imagePath);

        return redirect()->route('dashboard')->with('status', 'Product deleted successfully.');
    }

    private function storePicture(Request $request): ?string
    {
        if (! $request->hasFile('picture')) {
            return null;
        }

        $path = $request->file('picture')->store('products', 'public');

        if ($path === false) {
            throw ValidationException::withMessages([
                'picture' => 'The product picture could not be saved. Please try again.',
            ]);
        }

        return $path;
    }

    private function deletePicture(?string $path): void
    {
        if ($path === null) {
            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            Log::warning('Unable to delete a product picture from public storage.', ['path' => $path]);
        }
    }
}
