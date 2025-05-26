<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
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
     * Show the user profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $wantToReadBooks = $user->wantToReadBooks()->get();
        $currentlyReadingBooks = $user->currentlyReadingBooks()->get();
        $readBooks = $user->readBooks()->get();
        
        return view('profile.index', compact('user', 'wantToReadBooks', 'currentlyReadingBooks', 'readBooks'));
    }

    /**
     * Show the form for editing the user profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::delete('public/profile_pictures/' . $user->profile_picture);
            }
            
            // Store new profile picture
            $profilePicture = $request->file('profile_picture');
            $filename = time() . '.' . $profilePicture->getClientOriginalExtension();
            $profilePicture->storeAs('public/profile_pictures', $filename);
            
            $user->profile_picture = $filename;
        }
        
        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('current_password') && $request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
            
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
