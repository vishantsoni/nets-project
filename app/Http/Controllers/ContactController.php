<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function create()
    {
        return view("contact");
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone" => "nullable|string|max:20",
            "subject" => "required|string|max:255",
            "message" => "required|string",
        ]);

        return redirect()->route("contact")->with("success", "Thank you for contacting us. We will respond within 24 hours.");
    }
}
