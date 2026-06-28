<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMemberRequest;
use App\Models\TeamMember;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $teamMembers = TeamMember::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.team-members.index', compact('teamMembers', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.team-members.create', [
            'teamMember' => new TeamMember([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'team');
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
        }

        TeamMember::create($data);

        return to_route('admin.team-members.index')
            ->with('success', 'Team member created successfully.');
    }

    public function edit(TeamMember $teamMember): View
    {
        return view('admin.website.team-members.edit', compact('teamMember'));
    }

    public function update(TeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'team');
            $oldImage = $teamMember->image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
            $oldImage = $teamMember->image !== $selectedPath ? $teamMember->image : null;
        } elseif (MediaPicker::shouldClear($request, 'image')) {
            $data['image'] = null;
            $oldImage = $teamMember->image;
        }

        $teamMember->update($data);
        PublicImage::delete($oldImage);

        return to_route('admin.team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $image = $teamMember->image;

        $teamMember->delete();
        PublicImage::delete($image);

        return to_route('admin.team-members.index')
            ->with('success', 'Team member deleted successfully.');
    }

    public function toggleStatus(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->update(['status' => ! $teamMember->status]);

        return back()->with('success', 'Team member status updated successfully.');
    }
}
