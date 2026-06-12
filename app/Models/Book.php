<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'slug',
        'description',
        'price',
        'category_id',
        'cover_path',
        'file_path',
        'file_format',
        'accent_color',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Users who own this book (library entitlements).
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('order_id')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Case-insensitive search on title and author.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $like = '%'.mb_strtolower(trim($term)).'%';

        return $query->where(function (Builder $query) use ($like) {
            $query->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(author) LIKE ?', [$like]);
        });
    }

    public function scopeInCategory(Builder $query, ?string $categorySlug): Builder
    {
        if ($categorySlug === null || $categorySlug === '') {
            return $query;
        }

        return $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
    }

    /**
     * Sort by price (price_asc / price_desc), newest first otherwise.
     */
    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }
}
