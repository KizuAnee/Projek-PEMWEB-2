<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories = Category::withCount('books')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Display the specified category with its books.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Category $category)
    {
        $books = $category->books()->paginate(12);
        return view('categories.show', compact('category', 'books'));
    }
}
