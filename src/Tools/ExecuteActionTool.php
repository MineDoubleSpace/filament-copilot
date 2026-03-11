<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Enums\AuditAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ExecuteActionTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Execute a Filament action on a record or page. For destructive actions, the user will be asked for confirmation.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()->description('The resource slug')->required(),
            'action' => $schema->string()->description('The action name to execute')->required(),
            'record_id' => $schema->string()->description('The record ID for record-scoped actions'),
            'data' => $schema->string()->description('Optional JSON data to pass to the action'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $resourceClass = $this->resolveResource($request['resource']);

        if (! $resourceClass) {
            return "Resource '{$request['resource']}' not found.";
        }

        $actionName = $request['action'];
        $recordId = $request['record_id'];

        $this->audit(AuditAction::ActionExecuted, $resourceClass, $recordId, [
            'action' => $actionName,
        ]);

        // Return info about the action - actual execution happens through the Livewire component
        $lines = ["Action '{$actionName}' prepared for execution on {$resourceClass::getModelLabel()}."];

        if ($recordId) {
            $lines[] = "Target record: #{$recordId}";
        }

        $lines[] = 'The action will be dispatched to the frontend for execution with proper Filament lifecycle handling.';

        return implode("\n", $lines);
    }
}
