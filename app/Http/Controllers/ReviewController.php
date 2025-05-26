<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created review in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Book $book)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Check if user already has a review for this book
        $existingReview = Review::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('books.show', $book)
                ->with('error', 'You have already reviewed this book. You can edit your existing review.');
        }

        $review = new Review();
        $review->user_id = Auth::id();
        $review->book_id = $book->id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review added successfully.');
    }

    /**
     * Show the form for editing the specified review.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return redirect()->route('books.show', $review->book)
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified review from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review deleted successfully.');
    }
}
