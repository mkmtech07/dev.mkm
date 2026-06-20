<?php

namespace Tests\Feature\Admin;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_team_member_management(): void
    {
        $this->get(route('admin.team-members.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.team-members.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_team_members_and_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.team-members.store'), $this->validData([
                'image' => $this->fakeImage('member.png'),
            ]))
            ->assertRedirect(route('admin.team-members.index'))
            ->assertSessionHas('success');

        $teamMember = TeamMember::firstOrFail();
        $oldImage = $teamMember->image;

        $this->assertStringStartsWith('assets/images/team/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->patch(route('admin.team-members.toggle-status', $teamMember))
            ->assertSessionHas('success');

        $this->assertFalse($teamMember->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.team-members.update', $teamMember), $this->validData([
                'name' => 'Updated Member',
                'image' => $this->fakeImage('updated.png'),
            ]))
            ->assertRedirect(route('admin.team-members.index'));

        $teamMember->refresh();

        $this->assertSame('Updated Member', $teamMember->name);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($teamMember->image));

        $currentImage = $teamMember->image;

        $this->actingAs($user)
            ->delete(route('admin.team-members.destroy', $teamMember))
            ->assertRedirect(route('admin.team-members.index'));

        $this->assertSoftDeleted($teamMember);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_team_members_can_be_searched_and_paginated(): void
    {
        $user = User::factory()->create();

        TeamMember::create($this->validData(['name' => 'Riya Sharma']));

        for ($index = 1; $index <= 10; $index++) {
            TeamMember::create($this->validData([
                'name' => "Member {$index}",
                'bio' => "Profile for team member {$index}.",
                'email' => "member{$index}@example.com",
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.team-members.index', ['search' => 'Riya']))
            ->assertOk()
            ->assertSee('Riya Sharma')
            ->assertDontSee('Member 1');

        $this->actingAs($user)
            ->get(route('admin.team-members.index'))
            ->assertOk()
            ->assertViewHas('teamMembers', fn ($teamMembers) => $teamMembers->count() === 10 && $teamMembers->hasPages());
    }

    public function test_team_member_contact_and_social_links_are_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.team-members.create'))
            ->post(route('admin.team-members.store'), $this->validData([
                'email' => 'not-an-email',
                'linkedin_url' => 'not-a-url',
            ]))
            ->assertRedirect(route('admin.team-members.create'))
            ->assertSessionHasErrors(['email', 'linkedin_url']);
    }

    public function test_public_endpoint_returns_only_active_team_members_in_sort_order(): void
    {
        TeamMember::create($this->validData([
            'name' => 'Second member',
            'sort_order' => 20,
        ]));
        TeamMember::create($this->validData([
            'name' => 'Hidden member',
            'status' => false,
            'sort_order' => 0,
        ]));
        TeamMember::create($this->validData([
            'name' => 'First member',
            'image' => 'assets/images/team/first.jpg',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.team-members.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'First member')
            ->assertJsonPath('data.0.image_url', asset('assets/images/team/first.jpg'))
            ->assertJsonPath('data.1.name', 'Second member');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Riya Sharma',
            'designation' => 'Product Lead',
            'bio' => 'Riya turns complex business needs into focused products.',
            'email' => 'riya@example.com',
            'phone' => '+91 98765 43210',
            'facebook_url' => 'https://facebook.com/riya',
            'linkedin_url' => 'https://linkedin.com/in/riya',
            'twitter_url' => 'https://x.com/riya',
            'status' => true,
            'sort_order' => 10,
        ], $overrides);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function useTemporaryPublicPath(): void
    {
        $publicPath = storage_path('framework/testing/public/'.Str::uuid());

        File::ensureDirectoryExists($publicPath);
        $this->app->usePublicPath($publicPath);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($publicPath));
    }
}
