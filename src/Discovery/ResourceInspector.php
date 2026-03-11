<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Discovery;

use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotContext;
use EslamRedaDiv\FilamentCopilot\Macros\MacroRegistrar;
use Filament\Facades\Filament;
use Filament\Resources\Resource;

class ResourceInspector
{
    public function __construct(
        protected SchemaInspector $schemaInspector,
    ) {}

    /**
     * Discover all resources in the current panel and build context.
     */
    public function discoverResources(?string $panelId = null): array
    {
        $panel = $panelId
            ? Filament::getPanel($panelId)
            : Filament::getCurrentPanel();

        if (! $panel) {
            return [];
        }

        $resources = [];

        foreach ($panel->getResources() as $resourceClass) {
            $resources[] = $this->inspectResource($resourceClass);
        }

        return $resources;
    }

    /**
     * Inspect a single resource class and return its schema context.
     */
    public function inspectResource(string $resourceClass): array
    {
        /** @var class-string<Resource> $resourceClass */
        $modelClass = $resourceClass::getModel();
        $hasTrait = in_array(HasCopilotContext::class, class_uses_recursive($resourceClass));

        $data = [
            'resource' => $resourceClass,
            'model' => $modelClass,
            'label' => $resourceClass::getModelLabel(),
            'plural_label' => $resourceClass::getPluralModelLabel(),
            'navigation_label' => $resourceClass::getNavigationLabel(),
            'slug' => $resourceClass::getSlug(),
            'schema' => $this->schemaInspector->inspect($modelClass),
            'has_copilot_trait' => $hasTrait,
            'can_create' => $resourceClass::canCreate(),
        ];

        if ($hasTrait) {
            $data['copilot_readable_fields'] = $resourceClass::copilotReadableFields();
            $data['copilot_writable_fields'] = $resourceClass::copilotWritableFields();
            $data['copilot_can_create'] = $resourceClass::copilotCanCreate();
            $data['copilot_can_delete'] = $resourceClass::copilotCanDelete();
        }

        // Collect fields marked with needToAsk from form schema
        $data['need_to_ask_fields'] = $this->collectNeedToAskFields($resourceClass);

        return $data;
    }

    /**
     * Build AI-friendly resource descriptions for the system prompt.
     */
    public function buildResourceContext(?string $panelId = null): string
    {
        $resources = $this->discoverResources($panelId);

        if (empty($resources)) {
            return 'No resources available in this panel.';
        }

        $lines = ['Available Resources:'];

        foreach ($resources as $resource) {
            $lines[] = '';
            $lines[] = "## {$resource['plural_label']} ({$resource['slug']})";
            $lines[] = "Model: {$resource['model']}";
            $lines[] = $this->schemaInspector->describeForAi($resource['model']);

            if ($resource['has_copilot_trait']) {
                if ($resource['copilot_readable_fields']) {
                    $lines[] = 'Readable fields: '.implode(', ', $resource['copilot_readable_fields']);
                }
                if ($resource['copilot_writable_fields']) {
                    $lines[] = 'Writable fields: '.implode(', ', $resource['copilot_writable_fields']);
                }
                $lines[] = 'Can create: '.($resource['copilot_can_create'] ? 'yes' : 'no');
                $lines[] = 'Can delete: '.($resource['copilot_can_delete'] ? 'yes' : 'no');
            }

            if (! empty($resource['need_to_ask_fields'])) {
                $lines[] = 'Fields requiring user confirmation before filling (MUST ask user first): '.implode(', ', $resource['need_to_ask_fields']);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Collect form fields that have needToAsk=true via the MacroRegistrar static storage.
     */
    protected function collectNeedToAskFields(string $resourceClass): array
    {
        // needToAsk flags are stored on component instances via spl_object_id.
        // They are only populated when forms are rendered during the request lifecycle.
        // Return the globally tracked fields from MacroRegistrar if available.
        return MacroRegistrar::getTrackedNeedToAskFieldNames();
    }
}
