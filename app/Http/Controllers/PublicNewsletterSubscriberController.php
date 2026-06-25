<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicNewsletterSubscribeRequest;
use App\Models\NewsletterSubscriber;
use App\Services\AdminNotificationService;
use App\Services\EmailAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicNewsletterSubscriberController extends Controller
{
    public function subscribe(
        PublicNewsletterSubscribeRequest $request,
        AdminNotificationService $notifications,
        EmailAutomationService $automation,
    ): JsonResponse
    {
        $data = $request->safe()->except('website');
        [$message, $subscriber, $shouldNotify] = DB::transaction(function () use ($data, $request): array {
            $subscriber = NewsletterSubscriber::withTrashed()
                ->where('email', $data['email'])
                ->lockForUpdate()
                ->first();

            if ($subscriber && ! $subscriber->trashed() && $subscriber->status === 'subscribed') {
                return ['This email address is already subscribed.', $subscriber, false];
            }

            if ($subscriber && ! $subscriber->trashed() && $subscriber->status === 'blocked') {
                return ['This subscription cannot be updated. Please contact us for assistance.', $subscriber, false];
            }

            $attributes = [
                'name' => $data['name'] ?? $subscriber?->name,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? $subscriber?->phone,
                'source' => $data['source'],
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => $request->ip(),
                'user_agent' => str($request->userAgent())->limit(1000)->toString() ?: null,
                'status_active' => true,
            ];

            if ($subscriber) {
                $subscriber->restore();
                $subscriber->update($attributes);
            } else {
                $subscriber = NewsletterSubscriber::create($attributes);
            }

            return ['Thank you for subscribing to our newsletter.', $subscriber->fresh(), true];
        });

        if ($shouldNotify && $subscriber) {
            $notifications->notifyAllAdmins(
                'New Newsletter Subscriber',
                "{$subscriber->email} subscribed to newsletter.",
                'success',
                'newsletter',
                route('admin.newsletter-subscribers.show', $subscriber, false),
                ['newsletter_subscriber_id' => $subscriber->id]
            );

            $automation->sendNewsletterWelcome($subscriber);
        }

        return response()->json(['message' => $message], 201);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'unsubscribe_token' => ['nullable', 'string', 'size:64'],
        ]);
        $subscriber = NewsletterSubscriber::query()
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if ($subscriber && isset($validated['unsubscribe_token'])) {
            abort_unless(
                $subscriber->unsubscribe_token && hash_equals($subscriber->unsubscribe_token, $validated['unsubscribe_token']),
                422,
                'The unsubscribe details are invalid.'
            );
        }

        if ($subscriber) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'If this email was subscribed, it has now been unsubscribed.',
        ]);
    }
}
