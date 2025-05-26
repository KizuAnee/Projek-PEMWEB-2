<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the reviews for the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the bookshelves for the user.
     */
    public function bookshelves()
    {
        return $this->hasMany(Bookshelf::class);
    }

    /**
     * Get the books in the user's bookshelves.
     */
    public function books()
    {
        return $this->belongsToMany(Book::class, 'bookshelves')
            ->withPivot('shelf_type')
            ->withTimestamps();
    }

    /**
     * Get books that the user wants to read.
     */
    public function wantToReadBooks()
    {
        return $this->belongsToMany(Book::class, 'bookshelves')
            ->wherePivot('shelf_type', 'want_to_read')
            ->withTimestamps();
    }

    /**
     * Get books that the user is currently reading.
     */
    public function currentlyReadingBooks()
    {
        return $this->belongsToMany(Book::class, 'bookshelves')
            ->wherePivot('shelf_type', 'currently_reading')
            ->withTimestamps();
    }

    /**
     * Get books that the user has read.
     */
    public function readBooks()
    {
        return $this->belongsToMany(Book::class, 'bookshelves')
            ->wherePivot('shelf_type', 'read')
            ->withTimestamps();
    }
}
