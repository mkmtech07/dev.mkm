<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantSettingRequest;
use App\Models\Tenant;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSettingController extends Controller
{
    private const IMAGE_FIELDS = ['logo', 'favicon'];

    public function edit(Tenant $tenant): View
    {
        $setting = $tenant->setting()->firstOrCreate([
            'tenant_id' => $tenant->id,
        ], [
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => config('app.locale', 'en'),
        ]);

        return view('admin.tenants.settings.edit', compact('tenant', 'setting'));
    }

    public function update(TenantSettingRequest $request, Tenant $tenant): RedirectResponse
    {
        $setting = $tenant->setting()->firstOrCreate([
            'tenant_id' => $tenant->id,
        ], [
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => config('app.locale', 'en'),
        ]);

        $data = $request->safe()->except(self::IMAGE_FIELDS);
        $oldImages = [];

        foreach (self::IMAGE_FIELDS as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $data[$field] = PublicImage::store($request->file($field), 'tenants');
            if ($setting->{$field}) {
                $oldImages[] = $setting->{$field};
            }
        }

        $setting->update($data);

        foreach ($oldImages as $image) {
            PublicImage::delete($image);
        }

        return to_route('admin.tenants.settings.edit', $tenant)->with('success', 'Tenant settings updated successfully.');
    }
}
