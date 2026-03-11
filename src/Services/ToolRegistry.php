<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Services;

use EslamRedaDiv\FilamentCopilot\Tools\AskUserTool;
use EslamRedaDiv\FilamentCopilot\Tools\BaseTool;
use EslamRedaDiv\FilamentCopilot\Tools\CreatePlanTool;
use EslamRedaDiv\FilamentCopilot\Tools\CreateRecordTool;
use EslamRedaDiv\FilamentCopilot\Tools\DeleteRecordTool;
use EslamRedaDiv\FilamentCopilot\Tools\ExecuteActionTool;
use EslamRedaDiv\FilamentCopilot\Tools\ExecuteBulkActionTool;
use EslamRedaDiv\FilamentCopilot\Tools\ExportConversationTool;
use EslamRedaDiv\FilamentCopilot\Tools\FillFormTool;
use EslamRedaDiv\FilamentCopilot\Tools\FilterRecordsTool;
use EslamRedaDiv\FilamentCopilot\Tools\GetCurrentPageTool;
use EslamRedaDiv\FilamentCopilot\Tools\GetFormDataTool;
use EslamRedaDiv\FilamentCopilot\Tools\GetRecordTool;
use EslamRedaDiv\FilamentCopilot\Tools\GetSchemaInfoTool;
use EslamRedaDiv\FilamentCopilot\Tools\GetWidgetDataTool;
use EslamRedaDiv\FilamentCopilot\Tools\ListRecordsTool;
use EslamRedaDiv\FilamentCopilot\Tools\ListResourcesTool;
use EslamRedaDiv\FilamentCopilot\Tools\ListWidgetsTool;
use EslamRedaDiv\FilamentCopilot\Tools\NavigateToPageTool;
use EslamRedaDiv\FilamentCopilot\Tools\ReadInfolistTool;
use EslamRedaDiv\FilamentCopilot\Tools\RecallTool;
use EslamRedaDiv\FilamentCopilot\Tools\RememberTool;
use EslamRedaDiv\FilamentCopilot\Tools\SearchRecordsTool;
use EslamRedaDiv\FilamentCopilot\Tools\SortRecordsTool;
use EslamRedaDiv\FilamentCopilot\Tools\UpdateRecordTool;
use Illuminate\Database\Eloquent\Model;

class ToolRegistry
{
    protected array $globalTools = [];

    protected array $toolClasses = [
        ListRecordsTool::class,
        GetRecordTool::class,
        SearchRecordsTool::class,
        FilterRecordsTool::class,
        SortRecordsTool::class,
        CreateRecordTool::class,
        UpdateRecordTool::class,
        DeleteRecordTool::class,
        FillFormTool::class,
        GetFormDataTool::class,
        ExecuteActionTool::class,
        ExecuteBulkActionTool::class,
        NavigateToPageTool::class,
        GetCurrentPageTool::class,
        GetWidgetDataTool::class,
        RememberTool::class,
        RecallTool::class,
        GetSchemaInfoTool::class,
        ExportConversationTool::class,
        AskUserTool::class,
        CreatePlanTool::class,
        ReadInfolistTool::class,
        ListWidgetsTool::class,
        ListResourcesTool::class,
    ];

    /**
     * Register a global custom tool.
     */
    public function registerGlobal(string $toolClass): void
    {
        $this->globalTools[] = $toolClass;
    }

    /**
     * Build all tools configured for a panel/user context.
     */
    public function buildTools(string $panelId, Model $user, ?Model $tenant = null, ?string $conversationId = null): array
    {
        $tools = [];

        foreach ($this->toolClasses as $toolClass) {
            $tool = app($toolClass);

            if ($tool instanceof BaseTool) {
                $tool->forPanel($panelId)
                    ->forUser($user)
                    ->forTenant($tenant);
            }

            if ($conversationId && $tool instanceof CreatePlanTool) {
                $tool->forConversation($conversationId);
            }

            $tools[] = $tool;
        }

        foreach ($this->globalTools as $toolClass) {
            $tool = app($toolClass);

            if ($tool instanceof BaseTool) {
                $tool->forPanel($panelId)
                    ->forUser($user)
                    ->forTenant($tenant);
            }

            if ($conversationId && $tool instanceof CreatePlanTool) {
                $tool->forConversation($conversationId);
            }

            $tools[] = $tool;
        }

        return $tools;
    }

    /**
     * Get the list of registered tool classes.
     */
    public function getToolClasses(): array
    {
        return array_merge($this->toolClasses, $this->globalTools);
    }
}
