<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BookshelfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Halaman Beranda
Route::get('/', [HomeController::class, 'index'])->name('home');

// Autentikasi
Auth::routes();

// Profil Pengguna
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Buku
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/search', [BookController::class, 'search'])->name('books.search');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Admin routes for book management (would typically be protected by admin middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});

// Kategori
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Review (memerlukan autentikasi)
Route::middleware(['auth'])->group(function () {
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Rak Buku (memerlukan autentikasi)
Route::middleware(['auth'])->group(function () {
    Route::get('/bookshelves', [BookshelfController::class, 'index'])->name('bookshelves.index');
    Route::post('/books/{book}/shelf', [BookshelfController::class, 'store'])->name('bookshelves.store');
    Route::put('/bookshelves/{bookshelf}', [BookshelfController::class, 'update'])->name('bookshelves.update');
    Route::delete('/bookshelves/{bookshelf}', [BookshelfController::class, 'destroy'])->name('bookshelves.destroy');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
