<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Enums\AuditAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ExecuteBulkActionTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Execute a Filament bulk action on multiple records at once. Provide the resource slug, action name, and an array of record IDs.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()->description('The resource slug')->required(),
            'action' => $schema->string()->description('The bulk action name to execute')->required(),
            'record_ids' => $schema->string()->description('JSON array of record IDs to apply the bulk action on')->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $resource = (string) $request['resource'];
        $resourceClass = $this->resolveResource($resource);

        if (! $resourceClass) {
            return "Resource '{$resource}' not found.";
        }

        $actionName = (string) $request['action'];
        $recordIdsRaw = $request['record_ids'];
        $recordIds = is_string($recordIdsRaw) ? json_decode($recordIdsRaw, true) : $recordIdsRaw;

        if (! is_array($recordIds) || empty($recordIds)) {
            return 'Invalid record_ids. Provide a JSON array of record IDs.';
        }

        $this->audit(AuditAction::ActionExecuted, $resourceClass, null, [
            'action' => $actionName,
            'type' => 'bulk',
            'record_ids' => $recordIds,
            'record_count' => count($recordIds),
        ]);

        $lines = [
            "Bulk action '{$actionName}' prepared for execution on " . count($recordIds) . " {$resourceClass::getPluralModelLabel()}.",
            'Record IDs: ' . implode(', ', array_map(fn ($id) => "#{$id}", $recordIds)),
            'The bulk action will be dispatched to the frontend for execution with proper Filament lifecycle handling.',
        ];

        return implode("\n", $lines);
    }
}
