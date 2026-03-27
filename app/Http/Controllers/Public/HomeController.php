<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('portal.home');
    }

    public function contact()
    {
        return view('portal.contact');
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: handle contact form submission

        return redirect()->route('home')
            ->with('success', 'Your message has been sent.');
    }
}
