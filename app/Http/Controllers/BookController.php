<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $books = Book::with('category')->latest()->paginate(12);
        return view('books.index', compact('books'));
    }

    /**
     * Display the specified book.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Book $book)
    {
        $book->load(['category', 'reviews.user']);
        $userReview = null;
        
        if (auth()->check()) {
            $userReview = $book->reviews()->where('user_id', auth()->id())->first();
            $bookshelf = $book->users()->where('user_id', auth()->id())->first();
            $shelfType = $bookshelf ? $bookshelf->pivot->shelf_type : null;
            
            return view('books.show', compact('book', 'userReview', 'shelfType'));
        }
        
        return view('books.show', compact('book', 'userReview'));
    }

    /**
     * Search for books.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $categoryId = $request->input('category_id');
        
        $booksQuery = Book::query();
        
        if ($query) {
            $booksQuery->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('author', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }
        
        if ($categoryId) {
            $booksQuery->where('category_id', $categoryId);
        }
        
        $books = $booksQuery->with('category')->paginate(12)->appends([
            'query' => $query,
            'category_id' => $categoryId,
        ]);
        
        $categories = Category::all();
        
        return view('books.search', compact('books', 'categories', 'query', 'categoryId'));
    }

    /**
     * Show the form for creating a new book.
     * Note: This would typically be an admin function in a real Goodreads clone.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        $this->authorize('create', Book::class);
        
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    /**
     * Store a newly created book in storage.
     * Note: This would typically be an admin function in a real Goodreads clone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Book::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isbn' => 'nullable|string|max:20',
            'published_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'publisher' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        $book = new Book();
        $book->title = $request->title;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->isbn = $request->isbn;
        $book->published_year = $request->published_year;
        $book->publisher = $request->publisher;
        $book->category_id = $request->category_id;
        
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $filename = time() . '.' . $coverImage->getClientOriginalExtension();
            $coverImage->storeAs('public/covers', $filename);
            $book->cover_image = $filename;
        }
        
        $book->save();
        
        return redirect()->route('books.show', $book)->with('success', 'Book created successfully.');
    }

    /**
     * Show the form for editing the specified book.
     * Note: This would typically be an admin function in a real Goodreads clone.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified book in storage.
     * Note: This would typically be an admin function in a real Goodreads clone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isbn' => 'nullable|string|max:20',
            'published_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'publisher' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        $book->title = $request->title;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->isbn = $request->isbn;
        $book->published_year = $request->published_year;
        $book->publisher = $request->publisher;
        $book->category_id = $request->category_id;
        
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($book->cover_image) {
                Storage::delete('public/covers/' . $book->cover_image);
            }
            
            // Store new cover image
            $coverImage = $request->file('cover_image');
            $filename = time() . '.' . $coverImage->getClientOriginalExtension();
            $coverImage->storeAs('public/covers', $filename);
            $book->cover_image = $filename;
        }
        
        $book->save();
        
        return redirect()->route('books.show', $book)->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified book from storage.
     * Note: This would typically be an admin function in a real Goodreads clone.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        
        // Delete cover image if exists
        if ($book->cover_image) {
            Storage::delete('public/covers/' . $book->cover_image);
        }
        
        $book->delete();
        
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }
}
