<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Enums\AuditAction;
use Filament\Facades\Filament;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class NavigateToPageTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Navigate the user to a specific page or resource in the panel. This will actually navigate the browser to the page.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->string()->description('The page slug or resource slug to navigate to')->required(),
            'record_id' => $schema->string()->description('Optional record ID for resource detail/edit pages'),
            'action' => $schema->string()->description('Navigation action: list, create, view, edit. Defaults to list.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $page = (string) $request['page'];
        $recordId = $request['record_id'] !== null ? (string) $request['record_id'] : null;
        $action = (string) ($request['action'] ?? 'list');

        if (config('filament-copilot.audit.log_navigation', false)) {
            $this->audit(AuditAction::NavigatedTo, null, $recordId, [
                'page' => $page,
                'action' => $action,
            ]);
        }

        $url = $this->resolveUrl($page, $action, $recordId);

        if ($url === null) {
            return "Page or resource '{$page}' not found.";
        }

        // Return JSON with __navigate marker so the frontend can trigger real navigation
        return json_encode([
            '__navigate' => true,
            'url' => $url,
            'message' => "Navigating to: {$url}",
        ], JSON_UNESCAPED_SLASHES);
    }

    protected function resolveUrl(string $page, string $action, ?string $recordId): ?string
    {
        // Try to resolve as resource first
        $resourceClass = $this->resolveResource($page);

        if ($resourceClass) {
            return match ($action) {
                'create' => $resourceClass::getUrl('create'),
                'view' => $recordId ? $resourceClass::getUrl('view', ['record' => $recordId]) : $resourceClass::getUrl(),
                'edit' => $recordId ? $resourceClass::getUrl('edit', ['record' => $recordId]) : $resourceClass::getUrl(),
                default => $resourceClass::getUrl(),
            };
        }

        // Try as a page
        $panel = Filament::getCurrentPanel();
        if ($panel) {
            foreach ($panel->getPages() as $pageClass) {
                if ($pageClass::getSlug() === $page) {
                    return $pageClass::getUrl();
                }
            }
        }

        return null;
    }
}
