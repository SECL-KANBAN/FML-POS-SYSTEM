<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'is_available',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return self::imageUrlFor($this->image_path);
    }

    public static function imageUrlFor(?string $imagePath): ?string
    {
        if ($imagePath === null || $imagePath === '') {
            return null;
        }

        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        return Storage::disk('public')->url($imagePath);
    }
}
