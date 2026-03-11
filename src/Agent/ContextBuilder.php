<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Agent;

use EslamRedaDiv\FilamentCopilot\Discovery\PageInspector;
use EslamRedaDiv\FilamentCopilot\Discovery\ResourceInspector;
use EslamRedaDiv\FilamentCopilot\Discovery\WidgetInspector;
use EslamRedaDiv\FilamentCopilot\Models\CopilotAgentMemory;
use Illuminate\Database\Eloquent\Model;

class ContextBuilder
{
    protected string $panelId;

    protected ?Model $tenant = null;

    protected Model $user;

    protected ?string $customPrompt = null;

    protected bool $withPlanning = false;

    public function __construct(
        protected ResourceInspector $resourceInspector,
        protected PageInspector $pageInspector,
        protected WidgetInspector $widgetInspector,
    ) {}

    public function forPanel(string $panelId): static
    {
        $this->panelId = $panelId;

        return $this;
    }

    public function forTenant(?Model $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function forUser(Model $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function withCustomPrompt(?string $prompt): static
    {
        $this->customPrompt = $prompt;

        return $this;
    }

    public function withPlanning(bool $planning = true): static
    {
        $this->withPlanning = $planning;

        return $this;
    }

    public function build(): string
    {
        $sections = [];

        $sections[] = $this->buildBasePrompt();
        $sections[] = $this->buildResourceContext();
        $sections[] = $this->buildPageContext();
        $sections[] = $this->buildWidgetContext();
        $sections[] = $this->buildMemoryContext();

        if ($this->withPlanning) {
            $sections[] = $this->buildPlanningInstructions();
        }

        if ($this->customPrompt) {
            $sections[] = "## Additional Instructions\n{$this->customPrompt}";
        }

        return implode("\n\n", array_filter($sections));
    }

    protected function buildBasePrompt(): string
    {
        $prompt = config('filament-copilot.system_prompt');

        if ($prompt) {
            return $prompt;
        }

        return <<<'PROMPT'
You are a helpful Filament admin panel assistant. You help users manage their data
and navigate the admin panel efficiently.

## Guidelines
- Always respect user permissions. Never perform actions the user isn't authorized for.
- When modifying data, confirm the changes before saving unless specifically asked to save.
- Provide clear, concise responses.
- When listing records, format them in a readable way.
- If an action fails, explain why and suggest alternatives.
- Use the available tools to interact with panel resources, forms, and actions.
- Some fields are marked as "needToAsk". You MUST ask the user for the value of these fields
  before filling them in a form. Never assume or auto-fill needToAsk fields.
PROMPT;
    }

    protected function buildResourceContext(): string
    {
        return $this->resourceInspector->buildResourceContext($this->panelId);
    }

    protected function buildPageContext(): string
    {
        return $this->pageInspector->buildPageContext($this->panelId);
    }

    protected function buildWidgetContext(): string
    {
        return $this->widgetInspector->buildWidgetContext($this->panelId);
    }

    protected function buildMemoryContext(): string
    {
        if (! config('filament-copilot.memory.enabled', true)) {
            return '';
        }

        $memories = CopilotAgentMemory::recallAll(
            $this->user,
            $this->panelId,
            $this->tenant,
        );

        if (empty($memories)) {
            return '';
        }

        $lines = ['## Your Memories About This User'];
        foreach ($memories as $key => $value) {
            $lines[] = "- {$key}: {$value}";
        }

        return implode("\n", $lines);
    }

    protected function buildPlanningInstructions(): string
    {
        return <<<'PROMPT'
## Planning Mode
When the user asks you to perform a complex multi-step task:
1. First, create a plan outlining each step you will take.
2. Present the plan to the user for approval before executing.
3. Execute each step sequentially, reporting progress.
4. If a step fails, report the issue and ask how to proceed.
PROMPT;
    }
}
