<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicLeadRequest;
use App\Models\Lead;
use App\Services\AdminNotificationService;
use App\Services\EmailAutomationService;
use Illuminate\Http\JsonResponse;

class PublicLeadController extends Controller
{
    public function store(
        PublicLeadRequest $request,
        AdminNotificationService $notifications,
        EmailAutomationService $automation,
    ): JsonResponse
    {
        $data = $request->safe()->except('website');
        $data['status'] = 'new';
        $data['priority'] = 'medium';
        $data['status_active'] = true;
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = str($request->userAgent())->limit(1000)->toString() ?: null;

        $lead = Lead::create($data);

        $notifications->notifyAllAdmins(
            'New Lead Received',
            "{$lead->name} submitted a lead/enquiry.",
            'success',
            'leads',
            route('admin.leads.show', $lead, false),
            ['lead_id' => $lead->id]
        );

        $automation->sendLeadAutoReply($lead);
        $automation->sendLeadAdminAlert($lead);

        return response()->json([
            'message' => 'Thank you. Your enquiry has been received and our team will contact you soon.',
        ], 201);
    }
}
