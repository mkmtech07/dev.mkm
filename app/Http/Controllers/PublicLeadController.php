<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicLeadRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;

class PublicLeadController extends Controller
{
    public function store(PublicLeadRequest $request): JsonResponse
    {
        $data = $request->safe()->except('website');
        $data['status'] = 'new';
        $data['priority'] = 'medium';
        $data['status_active'] = true;
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = str($request->userAgent())->limit(1000)->toString() ?: null;

        Lead::create($data);

        return response()->json([
            'message' => 'Thank you. Your enquiry has been received and our team will contact you soon.',
        ], 201);
    }
}
