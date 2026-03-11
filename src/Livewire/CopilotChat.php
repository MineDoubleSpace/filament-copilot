<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Livewire;

use EslamRedaDiv\FilamentCopilot\Agent\CopilotAgent;
use EslamRedaDiv\FilamentCopilot\Agent\PlanningEngine;
use EslamRedaDiv\FilamentCopilot\Events\CopilotMessageSent;
use EslamRedaDiv\FilamentCopilot\Events\CopilotResponseReceived;
use EslamRedaDiv\FilamentCopilot\Models\CopilotConversation;
use EslamRedaDiv\FilamentCopilot\Models\CopilotPlan;
use EslamRedaDiv\FilamentCopilot\Services\ConversationManager;
use EslamRedaDiv\FilamentCopilot\Services\ExportService;
use EslamRedaDiv\FilamentCopilot\Services\RateLimitService;
use EslamRedaDiv\FilamentCopilot\Services\ToolRegistry;
use Filament\Facades\Filament;
use Livewire\Attributes\On;
use Livewire\Component;

class CopilotChat extends Component
{
    public bool $isOpen = false;

    public ?string $conversationId = null;

    public string $message = '';

    public array $messages = [];

    public bool $isLoading = false;

    public ?array $pendingPlan = null;

    public ?string $pendingToolCallId = null;

    public ?array $pendingQuestion = null;

    public array $conversations = [];

    public bool $streamingEnabled = false;

    public function mount(): void
    {
        $this->loadConversations();
        $this->streamingEnabled = (bool) config('filament-copilot.streaming.enabled', true);
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function getStreamUrl(): string
    {
        return route('filament-copilot.stream');
    }

    public function sendMessage(): void
    {
        $content = trim($this->message);

        if ($content === '') {
            return;
        }

        $this->message = '';

        // If there's a pending question, treat this as the answer
        if ($this->pendingQuestion) {
            $this->pendingQuestion = null;
        }

        // If streaming is enabled, let JavaScript handle it via SSE
        if ($this->streamingEnabled) {
            $this->messages[] = [
                'role' => 'user',
                'content' => $content,
            ];

            $this->dispatch('copilot-send-stream', [
                'message' => $content,
                'conversationId' => $this->conversationId,
                'panelId' => Filament::getCurrentPanel()?->getId(),
                'streamUrl' => $this->getStreamUrl(),
                'csrfToken' => csrf_token(),
            ]);

            return;
        }

        // Synchronous fallback
        $this->isLoading = true;

        $user = Filament::auth()->user();
        $panelId = Filament::getCurrentPanel()->getId();
        $tenant = Filament::getTenant();

        /** @var RateLimitService $rateLimitService */
        $rateLimitService = app(RateLimitService::class);

        if (config('filament-copilot.rate_limits.enabled') && ! $rateLimitService->canSendMessage($user, $panelId, $tenant)) {
            $this->messages[] = [
                'role' => 'system',
                'content' => __('filament-copilot::filament-copilot.rate_limit_exceeded'),
            ];
            $this->isLoading = false;

            return;
        }

        /** @var ConversationManager $conversationManager */
        $conversationManager = app(ConversationManager::class);

        if (! $this->conversationId) {
            $conversation = $conversationManager->create($user, $panelId, $tenant);
            $this->conversationId = $conversation->id;
        } else {
            $conversation = CopilotConversation::find($this->conversationId);
        }

        // Add user message
        $conversationManager->addUserMessage($conversation, $content);

        $this->messages[] = [
            'role' => 'user',
            'content' => $content,
        ];

        event(new CopilotMessageSent($conversation, $content, $panelId));

        try {
            $this->processAgentResponse($conversation, $user, $panelId, $tenant);
        } catch (\Throwable $e) {
            $this->messages[] = [
                'role' => 'system',
                'content' => __('filament-copilot::filament-copilot.error_occurred').': '.$e->getMessage(),
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Called from JavaScript after SSE streaming completes.
     */
    #[On('copilot-stream-complete')]
    public function handleStreamComplete(string $content, ?string $newConversationId = null): void
    {
        if ($newConversationId && ! $this->conversationId) {
            $this->conversationId = $newConversationId;
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content,
        ];

        $this->checkForAskUserResponse($content);
        $this->checkForPendingPlans();
        $this->loadConversations();
    }

    /**
     * Called from JavaScript when SSE streaming encounters an error.
     */
    #[On('copilot-stream-error')]
    public function handleStreamError(string $error): void
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => __('filament-copilot::filament-copilot.error_occurred').': '.$error,
        ];
    }

    /**
     * Called from JavaScript when a plan status update is received via SSE.
     */
    #[On('copilot-plan-status')]
    public function handlePlanStatus(string $id, string $status, int $currentStep, int $totalSteps, ?array $steps = null): void
    {
        if ($status === 'proposed') {
            $this->pendingPlan = [
                'id' => $id,
                'description' => '',
                'steps' => $steps ?? [],
                'current_step' => $currentStep,
                'total_steps' => $totalSteps,
            ];
        } elseif (in_array($status, ['executing', 'approved'])) {
            $this->pendingPlan = [
                'id' => $id,
                'description' => '',
                'steps' => $steps ?? [],
                'current_step' => $currentStep,
                'total_steps' => $totalSteps,
                'executing' => true,
            ];
        } elseif (in_array($status, ['completed', 'failed', 'rejected'])) {
            $this->pendingPlan = null;
        }
    }

    protected function processAgentResponse($conversation, $user, string $panelId, $tenant): void
    {
        /** @var ToolRegistry $toolRegistry */
        $toolRegistry = app(ToolRegistry::class);

        /** @var CopilotAgent $agent */
        $agent = app(CopilotAgent::class);

        $agent->forPanel($panelId)
            ->forUser($user)
            ->forTenant($tenant)
            ->withTools($toolRegistry->buildTools($panelId, $user, $tenant))
            ->withMessages($this->getConversationMessages($conversation));

        if (config('filament-copilot.agent.should_think', false)) {
            $agent->thinking();
        }

        if (config('filament-copilot.agent.should_plan', false)) {
            $agent->planning();
        }

        $provider = config('filament-copilot.provider', 'openai');
        $model = config('filament-copilot.model');

        $response = $agent->prompt(
            prompt: end($this->messages)['content'] ?? '',
            provider: $provider,
            model: $model,
        );

        $responseText = $response->text;
        $usage = $response->usage;

        /** @var ConversationManager $conversationManager */
        $conversationManager = app(ConversationManager::class);

        $assistantMessage = $conversationManager->addAssistantMessage(
            conversation: $conversation,
            content: $responseText,
            inputTokens: $usage->promptTokens ?? 0,
            outputTokens: $usage->completionTokens ?? 0,
        );

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $responseText,
        ];

        // Check if the response contains an ask_user request
        $this->checkForAskUserResponse($responseText);

        // Check for pending plans
        $this->checkForPendingPlans();

        // Record token usage
        if (config('filament-copilot.rate_limits.enabled')) {
            /** @var RateLimitService $rateLimitService */
            $rateLimitService = app(RateLimitService::class);
            $rateLimitService->recordTokenUsage(
                user: $user,
                panelId: $panelId,
                inputTokens: $usage->promptTokens ?? 0,
                outputTokens: $usage->completionTokens ?? 0,
                tenant: $tenant,
                conversationId: $conversation->id,
                model: $model,
                provider: $provider,
            );
        }

        event(new CopilotResponseReceived(
            $conversation,
            $assistantMessage,
            $usage->promptTokens ?? 0,
            $usage->completionTokens ?? 0,
        ));

        $this->loadConversations();
    }

    /**
     * Check if the agent response contains an ask_user tool response.
     */
    protected function checkForAskUserResponse(string $responseText): void
    {
        $decoded = json_decode($responseText, true);

        if (is_array($decoded) && ($decoded['type'] ?? null) === 'ask_user') {
            $this->pendingQuestion = $decoded;
        }
    }

    /**
     * Check for pending plans in the current conversation.
     */
    protected function checkForPendingPlans(): void
    {
        if (! $this->conversationId) {
            return;
        }

        $conversation = CopilotConversation::find($this->conversationId);

        if (! $conversation) {
            return;
        }

        /** @var PlanningEngine $planningEngine */
        $planningEngine = app(PlanningEngine::class);
        $activePlan = $planningEngine->getActivePlan($conversation);

        if ($activePlan && $activePlan->status->value === 'proposed') {
            $this->pendingPlan = [
                'id' => $activePlan->id,
                'description' => $activePlan->plan_content,
                'steps' => $activePlan->steps,
            ];
        }
    }

    /**
     * Respond to a pending question from AskUserTool.
     */
    public function respondToQuestion(string $answer): void
    {
        $this->pendingQuestion = null;
        $this->message = $answer;
        $this->sendMessage();
    }

    protected function getConversationMessages($conversation): array
    {
        /** @var ConversationManager $conversationManager */
        $conversationManager = app(ConversationManager::class);

        return $conversationManager->getMessagesForAgent($conversation);
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->pendingPlan = null;
        $this->pendingToolCallId = null;
        $this->pendingQuestion = null;
        $this->dispatch('copilot-conversation-changed', conversationId: null);
    }

    #[On('copilot-load-conversation')]
    public function loadConversation(string $conversationId): void
    {
        $conversation = CopilotConversation::with('messages')->find($conversationId);

        if (! $conversation) {
            return;
        }

        $this->conversationId = $conversationId;
        $this->messages = $conversation->messages
            ->map(fn ($m) => [
                'role' => $m->role->value,
                'content' => $m->content,
            ])
            ->toArray();
        $this->showHistory = false;
        $this->dispatch('copilot-conversation-changed', conversationId: $conversationId);
    }

    public function deleteConversation(string $conversationId): void
    {
        /** @var ConversationManager $conversationManager */
        $conversationManager = app(ConversationManager::class);
        $conversation = CopilotConversation::find($conversationId);

        if ($conversation) {
            $conversationManager->delete($conversation);
        }

        if ($this->conversationId === $conversationId) {
            $this->newConversation();
        }

        $this->loadConversations();
    }

    public function approvePlan(string $planId): void
    {
        $plan = CopilotPlan::find($planId);

        if ($plan) {
            /** @var PlanningEngine $planningEngine */
            $planningEngine = app(PlanningEngine::class);
            $planningEngine->approve($plan);
        }

        $this->pendingPlan = null;
    }

    public function rejectPlan(string $planId): void
    {
        $plan = CopilotPlan::find($planId);

        if ($plan) {
            /** @var PlanningEngine $planningEngine */
            $planningEngine = app(PlanningEngine::class);
            $planningEngine->reject($plan);
        }

        $this->pendingPlan = null;
    }

    public function toggleHistory(): void
    {
        $this->dispatch('copilot-toggle-sidebar');
    }

    protected function loadConversations(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        $panelId = Filament::getCurrentPanel()?->getId();
        $tenant = Filament::getTenant();

        if (! $panelId) {
            return;
        }

        /** @var ConversationManager $conversationManager */
        $conversationManager = app(ConversationManager::class);

        $this->conversations = $conversationManager
            ->getConversations($user, $panelId, $tenant)
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'updated_at' => $c->updated_at->diffForHumans(),
            ])
            ->toArray();
    }

    #[On('copilot-quick-action')]
    public function handleQuickAction(string $prompt): void
    {
        $this->message = $prompt;
        $this->sendMessage();
    }

    public function exportConversation(): \Symfony\Component\HttpFoundation\StreamedResponse|null
    {
        if (! $this->conversationId || ! config('filament-copilot.export.enabled', true)) {
            return null;
        }

        /** @var ExportService $exportService */
        $exportService = app(ExportService::class);
        $markdown = $exportService->toMarkdown($this->conversationId);

        if (! $markdown) {
            return null;
        }

        $filename = 'copilot-conversation-'.now()->format('Y-m-d-His').'.md';

        return response()->streamDownload(function () use ($markdown) {
            echo $markdown;
        }, $filename, ['Content-Type' => 'text/markdown']);
    }

    public function render()
    {
        return view('filament-copilot::livewire.copilot-chat');
    }
}
