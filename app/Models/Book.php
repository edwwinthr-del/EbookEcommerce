<?php

namespace App\Models;

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
}
