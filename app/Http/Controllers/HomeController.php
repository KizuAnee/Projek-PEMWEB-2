<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $latestBooks = Book::with('category')->latest()->take(6)->get();
        $popularBooks = Book::withCount('reviews')
            ->orderBy('reviews_count', 'desc')
            ->take(6)
            ->get();
            
        return view('home', compact('latestBooks', 'popularBooks'));
    }
}
