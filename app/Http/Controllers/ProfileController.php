<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->latest()->get();
        return view('profile', compact('addresses'));
    }
}