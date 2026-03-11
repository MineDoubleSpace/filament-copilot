<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Contracts;

use Laravel\Ai\Contracts\Tool;

interface CopilotPage
{
    /**
     * A description of what this page shows, shown to the AI agent.
     */
    public static function copilotPageDescription(): ?string;

    /**
     * Return the copilot tools available for this page.
     *
     * @return array<Tool>
     */
    public static function copilotTools(): array;
}
