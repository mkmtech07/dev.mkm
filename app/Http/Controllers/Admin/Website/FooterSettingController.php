<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\FooterSettingRequest;
use App\Models\FooterSetting;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FooterSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.website.footer.settings.edit', [
            'footerSetting' => $this->settings(),
        ]);
    }

    public function update(FooterSettingRequest $request): RedirectResponse
    {
        $footerSetting = $this->settings();
        $data = $request->safe()->except(['footer_logo', 'remove_footer_logo']);
        $oldLogo = null;

        if ($request->hasFile('footer_logo')) {
            $data['footer_logo'] = PublicImage::store($request->file('footer_logo'), 'footer');
            $oldLogo = $footerSetting->footer_logo;
        } elseif ($request->boolean('remove_footer_logo') && $footerSetting->footer_logo) {
            $data['footer_logo'] = null;
            $oldLogo = $footerSetting->footer_logo;
        }

        $footerSetting->update($data);
        PublicImage::delete($oldLogo);

        return back()->with('success', 'Footer settings updated successfully.');
    }

    private function settings(): FooterSetting
    {
        return FooterSetting::firstOrCreate([], [
            'newsletter_status' => false,
            'status' => true,
        ]);
    }
}
