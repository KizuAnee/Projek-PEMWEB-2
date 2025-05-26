<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookshelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookshelfController extends Controller
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
     * Display a listing of the user's bookshelves.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $wantToReadBooks = $user->wantToReadBooks()->with('category')->get();
        $currentlyReadingBooks = $user->currentlyReadingBooks()->with('category')->get();
        $readBooks = $user->readBooks()->with('category')->get();
        
        return view('bookshelves.index', compact('wantToReadBooks', 'currentlyReadingBooks', 'readBooks'));
    }

    /**
     * Store a newly created bookshelf entry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Book $book)
    {
        $request->validate([
            'shelf_type' => 'required|in:want_to_read,currently_reading,read',
        ]);

        // Check if book is already in any of user's shelves
        $existingBookshelf = Bookshelf::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingBookshelf) {
            // Update existing bookshelf entry
            $existingBookshelf->shelf_type = $request->shelf_type;
            $existingBookshelf->save();
            
            $message = 'Book moved to "' . $this->getShelfName($request->shelf_type) . '" shelf.';
        } else {
            // Create new bookshelf entry
            $bookshelf = new Bookshelf();
            $bookshelf->user_id = Auth::id();
            $bookshelf->book_id = $book->id;
            $bookshelf->shelf_type = $request->shelf_type;
            $bookshelf->save();
            
            $message = 'Book added to "' . $this->getShelfName($request->shelf_type) . '" shelf.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update the specified bookshelf entry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bookshelf  $bookshelf
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Bookshelf $bookshelf)
    {
        $this->authorize('update', $bookshelf);

        $request->validate([
            'shelf_type' => 'required|in:want_to_read,currently_reading,read',
        ]);

        $bookshelf->shelf_type = $request->shelf_type;
        $bookshelf->save();

        return redirect()->back()->with('success', 'Shelf updated successfully.');
    }

    /**
     * Remove the specified bookshelf entry from storage.
     *
     * @param  \App\Models\Bookshelf  $bookshelf
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Bookshelf $bookshelf)
    {
        $this->authorize('delete', $bookshelf);

        $bookshelf->delete();

        return redirect()->back()->with('success', 'Book removed from shelf.');
    }
    
    /**
     * Get the display name for a shelf type.
     *
     * @param  string  $shelfType
     * @return string
     */
    private function getShelfName($shelfType)
    {
        $names = [
            'want_to_read' => 'Want to Read',
            'currently_reading' => 'Currently Reading',
            'read' => 'Read',
        ];
        
        return $names[$shelfType] ?? $shelfType;
    }
}
