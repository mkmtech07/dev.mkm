<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\AdminNotificationService;
use App\Services\EmailAutomationService;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    public function store(
        StoreContactMessageRequest $request,
        AdminNotificationService $notifications,
        EmailAutomationService $automation,
    ): JsonResponse
    {
        $contactMessage = ContactMessage::create([
            ...$request->validated(),
            'source' => 'contact-page',
        ]);

        $notifications->notifyAllAdmins(
            'New Contact Message',
            "{$contactMessage->name} sent a contact message.",
            'info',
            'contact_messages',
            route('admin.contact-messages.show', $contactMessage, false),
            ['contact_message_id' => $contactMessage->id]
        );

        $automation->sendContactAutoReply($contactMessage);
        $automation->sendContactAdminAlert($contactMessage);

        return response()->json([
            'message' => 'Thank you for contacting us. We will get back to you soon.',
        ], 201);
    }
}
