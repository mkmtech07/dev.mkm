<?php

namespace App\Jobs;

use App\Services\EmailAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAutomatedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly ?string $recipient,
        public readonly string $templateTypeOrSlug,
        public readonly array $data = [],
        public readonly ?string $mailType = null,
    ) {
    }

    public function handle(EmailAutomationService $automation): void
    {
        $automation->sendNow($this->recipient, $this->templateTypeOrSlug, $this->data, $this->mailType);
    }
}
