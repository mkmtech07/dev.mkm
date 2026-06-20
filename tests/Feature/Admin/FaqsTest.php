<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_faq_management(): void
    {
        $this->get(route('admin.faqs.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.faqs.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_faqs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.faqs.store'), $this->validData())
            ->assertRedirect(route('admin.faqs.index'))
            ->assertSessionHas('success');

        $faq = Faq::firstOrFail();

        $this->actingAs($user)
            ->patch(route('admin.faqs.toggle-status', $faq))
            ->assertSessionHas('success');

        $this->assertFalse($faq->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.faqs.update', $faq), $this->validData([
                'question' => 'Can I update my subscription later?',
                'sort_order' => 5,
            ]))
            ->assertRedirect(route('admin.faqs.index'));

        $faq->refresh();

        $this->assertSame('Can I update my subscription later?', $faq->question);
        $this->assertSame(5, $faq->sort_order);

        $this->actingAs($user)
            ->delete(route('admin.faqs.destroy', $faq))
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertSoftDeleted($faq);
    }

    public function test_faqs_can_be_searched_and_paginated(): void
    {
        $user = User::factory()->create();
        Faq::create($this->validData([
            'question' => 'How do refunds work?',
            'category' => 'Payments',
        ]));

        for ($index = 1; $index <= 10; $index++) {
            Faq::create($this->validData([
                'question' => "General question {$index}?",
                'answer' => "General answer {$index}.",
                'sort_order' => $index,
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.faqs.index', ['search' => 'refunds']))
            ->assertOk()
            ->assertSee('How do refunds work?')
            ->assertDontSee('General question 1?');

        $this->actingAs($user)
            ->get(route('admin.faqs.index'))
            ->assertOk()
            ->assertViewHas('faqs', fn ($faqs) => $faqs->count() === 10 && $faqs->hasPages());
    }

    public function test_faq_validation_requires_question_answer_and_valid_sort_order(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.faqs.create'))
            ->post(route('admin.faqs.store'), $this->validData([
                'question' => '',
                'answer' => '',
                'sort_order' => -1,
            ]))
            ->assertRedirect(route('admin.faqs.create'))
            ->assertSessionHasErrors(['question', 'answer', 'sort_order']);
    }

    public function test_public_endpoint_returns_only_active_faqs_in_sort_order(): void
    {
        Faq::create($this->validData([
            'question' => 'Second question?',
            'sort_order' => 20,
        ]));
        Faq::create($this->validData([
            'question' => 'Hidden question?',
            'status' => false,
            'sort_order' => 0,
        ]));
        Faq::create($this->validData([
            'question' => 'First question?',
            'category' => 'Getting Started',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.faqs.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.question', 'First question?')
            ->assertJsonPath('data.0.category', 'Getting Started')
            ->assertJsonPath('data.1.question', 'Second question?');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'question' => 'Can I change my plan later?',
            'answer' => 'Yes. You can move to another plan whenever your business needs change.',
            'category' => 'Subscriptions',
            'status' => true,
            'sort_order' => 10,
        ], $overrides);
    }
}
