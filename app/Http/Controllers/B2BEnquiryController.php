<?php

namespace App\Http\Controllers;

use App\Models\B2BEnquiry;
use App\Models\B2BEnquiryItem;
use Illuminate\Http\Request;

class B2BEnquiryController extends Controller
{
    public function create()
    {
        return view('b2b-enquiry');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone" => "required|string|max:20",
            "company" => "required|string|max:255",
            "designation" => "nullable|string|max:255",
            "subject" => "required|string|max:255",
            "description" => "required|string",
            "items" => "nullable|array",
        ]);

        $year = now()->year;
        $lastId = B2BEnquiry::max("id") + 1;

        $enquiry = B2BEnquiry::create(array_merge($validated, [
            "enquiry_number" => "ENQ-" . $year . "-" . strtoupper(\Illuminate\Support\Str::random(6)),
            "status" => "new",
            "institute_id" => null,
        ]));

        if ($request->has("items")) {
            foreach ($request->input("items") as $item) {
                B2BEnquiryItem::create([
                    "enquiry_id" => $enquiry->id,
                    "item_name" => $item["name"] ?? "",
                    "quantity" => $item["quantity"] ?? 1,
                    "remarks" => $item["remarks"] ?? null,
                ]);
            }
        }

        return redirect()->route("b2b-enquiry")->with("success", "Your enquiry has been submitted successfully. We will contact you within 24 hours.");
    }
}
