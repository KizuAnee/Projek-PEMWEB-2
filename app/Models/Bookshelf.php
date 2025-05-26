<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookshelf extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'shelf_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'shelf_type' => 'string',
    ];

    /**
     * Get the user that owns the bookshelf entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that belongs to the bookshelf entry.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
