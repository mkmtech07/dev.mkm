<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = in_array($request->query('status'), ['all', 'read', 'unread'], true)
            ? $request->query('status')
            : 'all';

        $contactMessages = ContactMessage::query()
            ->when($status === 'read', fn ($query) => $query->where('is_read', true))
            ->when($status === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.website.contact-messages.index', compact(
            'contactMessages',
            'search',
            'status'
        ));
    }

    public function show(ContactMessage $contactMessage): View
    {
        return view('admin.website.contact-messages.show', compact('contactMessage'));
    }

    public function update(
        UpdateContactMessageRequest $request,
        ContactMessage $contactMessage
    ): RedirectResponse {
        $contactMessage->update($request->validated());

        return back()->with('success', 'Internal notes updated successfully.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return to_route('admin.contact-messages.index')
            ->with('success', 'Contact message deleted successfully.');
    }

    public function toggleRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back()->with('success', 'Contact message status updated successfully.');
    }

    public function convertToLead(ContactMessage $contactMessage): RedirectResponse
    {
        $lead = Lead::create([
            'name' => $contactMessage->name,
            'email' => $contactMessage->email,
            'phone' => $contactMessage->phone,
            'subject' => $contactMessage->subject,
            'message' => $contactMessage->message,
            'source' => 'contact_form',
            'status' => 'new',
            'priority' => 'medium',
            'status_active' => true,
        ]);

        $contactMessage->update(['is_read' => true]);

        return to_route('admin.leads.show', $lead)
            ->with('success', 'Contact message converted to a lead successfully.');
    }
}
