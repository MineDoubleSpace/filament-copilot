<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Discovery\ResourceInspector;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListResourcesTool extends BaseTool
{
    public function __construct(
        protected ResourceInspector $resourceInspector,
    ) {}

    public function description(): Stringable|string
    {
        return 'List all available resources in the current panel with their descriptions. Use get_tools with a resource class to discover its copilot tools.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $resources = $this->resourceInspector->discoverResources($this->panelId ?? null);

        if (empty($resources)) {
            return 'No resources available in this panel.';
        }

        $lines = ['Available Resources:', ''];

        foreach ($resources as $resource) {
            $line = '- ' . $resource['plural_label'] . ' (' . $resource['resource'] . ')';

            if (! empty($resource['copilot_description'])) {
                $line .= '  ' . $resource['copilot_description'];
            }

            if (! empty($resource['has_tools'])) {
                $line .= ' [has copilot tools]';
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
