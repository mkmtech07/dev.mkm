<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        ContactMessage::create([
            ...$request->validated(),
            'source' => 'contact-page',
        ]);

        return response()->json([
            'message' => 'Thank you for contacting us. We will get back to you soon.',
        ], 201);
    }
}
