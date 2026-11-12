<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductImageStorage
{
    public function store(UploadedFile $file): string
    {
        return match (config('product_images.driver')) {
            'local' => $this->storeLocally($file),
            'vercel_blob' => $this->storeInVercelBlob($file),
            default => throw new RuntimeException('Unsupported product image storage driver.'),
        };
    }

    public function delete(?string $path): bool
    {
        if ($path === null) {
            return true;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            if (! $this->isVercelBlobUrl($path)) {
                return false;
            }

            $token = $this->token();

            try {
                return Http::timeout(15)
                    ->withToken($token)
                    ->withHeaders($this->apiHeaders($token))
                    ->asJson()
                    ->post($this->apiUrl().'/delete', ['urls' => [$path]])
                    ->successful();
            } catch (ConnectionException $exception) {
                Log::warning('Vercel Blob image deletion request failed.', ['error' => $exception->getMessage()]);

                return false;
            }
        }

        return Storage::disk('public')->delete($path);
    }

    private function storeLocally(UploadedFile $file): string
    {
        $path = $file->store('products', 'public');

        if ($path === false) {
            throw ValidationException::withMessages([
                'picture' => 'The product picture could not be saved. Please try again.',
            ]);
        }

        return $path;
    }

    private function storeInVercelBlob(UploadedFile $file): string
    {
        $token = $this->token();
        $pathname = 'products/'.Str::uuid().'.'.$file->extension();
        $contentType = $file->getMimeType() ?? 'application/octet-stream';
        $url = $this->apiUrl().'/'.'?'.http_build_query(['pathname' => $pathname]);

        $response = Http::timeout(30)
            ->withToken($token)
            ->withHeaders([
                ...$this->apiHeaders($token),
                'x-content-type' => $contentType,
                'x-vercel-blob-access' => 'public',
                'x-add-random-suffix' => '0',
            ])
            ->withBody($file->get(), $contentType)
            ->send('PUT', $url);

        $response->throw();

        $blobUrl = $response->json('url');

        if (! is_string($blobUrl) || ! $this->isVercelBlobUrl($blobUrl)) {
            throw new RuntimeException('Vercel Blob returned an invalid product image URL.');
        }

        return $blobUrl;
    }

    private function token(): string
    {
        $token = config('product_images.vercel_blob.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('BLOB_READ_WRITE_TOKEN is required for Vercel Blob image storage.');
        }

        $parts = explode('_', $token);

        if (count($parts) < 5 || array_slice($parts, 0, 3) !== ['vercel', 'blob', 'rw'] || $parts[3] === '') {
            throw new RuntimeException('BLOB_READ_WRITE_TOKEN is not a valid Vercel Blob read-write token.');
        }

        return $token;
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(string $token): array
    {
        $parts = explode('_', $token);

        return [
            'x-vercel-blob-store-id' => $parts[3],
            'x-api-version' => (string) config('product_images.vercel_blob.api_version'),
            'x-api-blob-request-id' => (string) Str::uuid(),
            'x-api-blob-request-attempt' => '0',
        ];
    }

    private function apiUrl(): string
    {
        return rtrim((string) config('product_images.vercel_blob.api_url'), '/');
    }

    private function isVercelBlobUrl(string $url): bool
    {
        $parts = parse_url($url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && isset($parts['host'])
            && Str::endsWith(strtolower($parts['host']), '.public.blob.vercel-storage.com');
    }
}
