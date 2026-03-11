<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Contracts;

use Laravel\Ai\Contracts\Tool;

interface CopilotWidget
{
    /**
     * A description of what this widget shows, shown to the AI agent.
     */
    public static function copilotWidgetDescription(): ?string;

    /**
     * Return the copilot tools available for this widget.
     *
     * @return array<Tool>
     */
    public static function copilotTools(): array;
}
