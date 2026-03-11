<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Enums\AuditAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetFormDataTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Get the current form field values for a record being edited.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()->description('The resource slug')->required(),
            'record_id' => $schema->string()->description('The record ID to get form data for')->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $resourceClass = $this->resolveResource($request['resource']);

        if (! $resourceClass) {
            return "Resource '{$request['resource']}' not found.";
        }

        $modelClass = $resourceClass::getModel();
        $record = $modelClass::find($request['record_id']);

        if (! $record) {
            return "Record #{$request['record_id']} not found.";
        }

        if (! $this->authorizeView($resourceClass, $record)) {
            return 'You are not authorized to view this record.';
        }

        $this->audit(AuditAction::RecordRead, $resourceClass, (string) $record->getKey());

        $fillable = $record->getFillable();
        $data = $record->only($fillable);

        $lines = ["Form data for {$resourceClass::getModelLabel()} #{$record->getKey()}:", ''];

        foreach ($data as $field => $value) {
            $display = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? 'null');
            $lines[] = "  {$field}: {$display}";
        }

        return implode("\n", $lines);
    }
}
