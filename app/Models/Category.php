<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'group', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (filled($category->slug) || blank($category->name)) {
                return;
            }

            $base = Str::slug($category->name) ?: 'category';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->when($category->exists, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }
            $category->slug = $slug;
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
