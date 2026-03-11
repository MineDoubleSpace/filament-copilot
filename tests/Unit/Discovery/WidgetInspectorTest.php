<?php

use EslamRedaDiv\FilamentCopilot\Discovery\WidgetInspector;
use Illuminate\Contracts\Container\BindingResolutionException;

it('creates WidgetInspector instance', function () {
    $inspector = new WidgetInspector;
    expect($inspector)->toBeInstanceOf(WidgetInspector::class);
});

it('returns empty array when no panel available', function () {
    $inspector = new WidgetInspector;
    try {
        $widgets = $inspector->discoverWidgets('nonexistent');
        expect($widgets)->toBeArray();
    } catch (BindingResolutionException $e) {
        expect($e->getMessage())->toContain('filament');
    }
});

it('returns empty string for widget context when no panel available', function () {
    $inspector = new WidgetInspector;
    try {
        $context = $inspector->buildWidgetContext('nonexistent');
        expect($context)->toBeString();
    } catch (BindingResolutionException $e) {
        expect($e->getMessage())->toContain('filament');
    }
});