<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Discovery;

use EslamRedaDiv\FilamentCopilot\Contracts\CopilotWidget;
use Filament\Facades\Filament;

class WidgetInspector
{
    /**
     * Discover all widgets in the panel that implement CopilotWidget.
     */
    public function discoverWidgets(?string $panelId = null): array
    {
        $panel = $panelId
            ? Filament::getPanel($panelId)
            : Filament::getCurrentPanel();

        if (! $panel) {
            return [];
        }

        $widgets = [];

        foreach ($panel->getWidgets() as $widgetClass) {
            if (! is_subclass_of($widgetClass, CopilotWidget::class)) {
                continue;
            }

            $hasTools = false;
            try {
                $description = $widgetClass::copilotWidgetDescription();
                $hasTools = ! empty($widgetClass::copilotTools());
            } catch (\Throwable) {
                $description = null;
            }

            $widgets[] = [
                'widget' => $widgetClass,
                'name' => class_basename($widgetClass),
                'description' => $description,
                'has_tools' => $hasTools,
            ];
        }

        return $widgets;
    }

    /**
     * Build AI-friendly widget descriptions for the system prompt.
     */
    public function buildWidgetContext(?string $panelId = null): string
    {
        $widgets = $this->discoverWidgets($panelId);

        if (empty($widgets)) {
            return '';
        }

        $lines = ['## Available Widgets'];

        foreach ($widgets as $widget) {
            $line = '- ' . $widget['name'] . ' (' . $widget['widget'] . ')';

            if (! empty($widget['description'])) {
                $line .= ': ' . $widget['description'];
            }

            if ($widget['has_tools']) {
                $line .= ' [has copilot tools]';
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
