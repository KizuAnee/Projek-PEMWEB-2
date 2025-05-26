<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'cover_image',
        'isbn',
        'published_year',
        'publisher',
        'category_id',
    ];

    /**
     * Get the category that owns the book.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the reviews for the book.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the users who have this book in their bookshelves.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'bookshelves')
            ->withPivot('shelf_type')
            ->withTimestamps();
    }

    /**
     * Get the average rating for the book.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    /**
     * Get the total number of reviews for the book.
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }
}
