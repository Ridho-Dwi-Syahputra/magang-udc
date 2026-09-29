<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // Slug dibikin otomatis dari nama, jadi client nggak perlu ngirim field ini
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (! $category->slug || $category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
