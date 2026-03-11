<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Tools;

use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotWidgetContext;
use EslamRedaDiv\FilamentCopilot\Contracts\ProvidesWidgetData;
use Filament\Facades\Filament;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetWidgetDataTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Get data from a dashboard widget. Works with widgets that implement ProvidesWidgetData or use the HasCopilotWidgetContext trait.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'widget' => $schema->string()->description('The widget class name or identifier')->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $widgetName = (string) $request['widget'];
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return 'No active panel.';
        }

        foreach ($panel->getWidgets() as $widgetClass) {
            if (class_basename($widgetClass) === $widgetName || $widgetClass === $widgetName) {
                $implementsInterface = is_subclass_of($widgetClass, ProvidesWidgetData::class)
                    || in_array(ProvidesWidgetData::class, class_implements($widgetClass) ?: []);
                $hasTrait = in_array(HasCopilotWidgetContext::class, class_uses_recursive($widgetClass));

                if ($implementsInterface || $hasTrait) {
                    $widget = app($widgetClass);
                    $data = $widget->copilotWidgetData();
                    $description = $widget->copilotWidgetDescription();

                    $lines = [
                        "Widget: {$description}",
                        '',
                        'Data:',
                    ];

                    foreach ($data as $key => $value) {
                        $display = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                        $lines[] = "  {$key}: {$display}";
                    }

                    return implode("\n", $lines);
                }

                return "Widget '{$widgetName}' does not expose data to Copilot. Add the HasCopilotWidgetContext trait or implement ProvidesWidgetData.";
            }
        }

        return "Widget '{$widgetName}' not found. Available widgets: ".
            implode(', ', array_map(fn ($w) => class_basename($w), $panel->getWidgets()));
    }
}
