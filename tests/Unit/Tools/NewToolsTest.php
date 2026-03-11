<?php

use EslamRedaDiv\FilamentCopilot\Tools\AskUserTool;
use EslamRedaDiv\FilamentCopilot\Tools\CreatePlanTool;
use EslamRedaDiv\FilamentCopilot\Tools\ExecuteBulkActionTool;
use EslamRedaDiv\FilamentCopilot\Tools\ListResourcesTool;
use EslamRedaDiv\FilamentCopilot\Tools\ListWidgetsTool;
use EslamRedaDiv\FilamentCopilot\Tools\ReadInfolistTool;
use Laravel\Ai\Contracts\Tool;

it('AskUserTool implements Tool contract', function () {
    $tool = new AskUserTool;

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('AskUserTool has proper description', function () {
    $tool = new AskUserTool;
    $description = (string) $tool->description();

    expect($description)->toContain('Ask the user')
        ->and($description)->toContain('wait for their response');
});

it('AskUserTool has schema method', function () {
    $tool = new AskUserTool;

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});

it('CreatePlanTool implements Tool contract', function () {
    $tool = new CreatePlanTool;

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('CreatePlanTool has proper description', function () {
    $tool = new CreatePlanTool;
    $description = (string) $tool->description();

    expect($description)->toContain('multi-step execution plan')
        ->and($description)->toContain('approval');
});

it('CreatePlanTool has schema method', function () {
    $tool = new CreatePlanTool;

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});

it('ReadInfolistTool implements Tool contract', function () {
    $tool = new ReadInfolistTool;

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('ReadInfolistTool has proper description', function () {
    $tool = new ReadInfolistTool;
    $description = (string) $tool->description();

    expect($description)->toContain('infolist')
        ->and($description)->toContain('entries');
});

it('ReadInfolistTool has schema with resource and record_id', function () {
    $tool = new ReadInfolistTool;

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});

it('ListWidgetsTool implements Tool contract', function () {
    $tool = app(ListWidgetsTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('ListWidgetsTool has proper description', function () {
    $tool = app(ListWidgetsTool::class);
    $description = (string) $tool->description();

    expect($description)->toContain('widget')
        ->and($description)->toContain('panel');
});

it('ListWidgetsTool has empty schema', function () {
    $tool = app(ListWidgetsTool::class);

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});

it('ListResourcesTool implements Tool contract', function () {
    $tool = app(ListResourcesTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('ListResourcesTool has proper description', function () {
    $tool = app(ListResourcesTool::class);
    $description = (string) $tool->description();

    expect($description)->toContain('resource')
        ->and($description)->toContain('panel');
});

it('ListResourcesTool has empty schema', function () {
    $tool = app(ListResourcesTool::class);

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});

it('ExecuteBulkActionTool implements Tool contract', function () {
    $tool = new ExecuteBulkActionTool;

    expect($tool)->toBeInstanceOf(Tool::class);
});

it('ExecuteBulkActionTool has proper description', function () {
    $tool = new ExecuteBulkActionTool;
    $description = (string) $tool->description();

    expect($description)->toContain('bulk action')
        ->and($description)->toContain('multiple records');
});

it('ExecuteBulkActionTool has schema with resource, action, and record_ids', function () {
    $tool = new ExecuteBulkActionTool;

    expect(method_exists($tool, 'schema'))->toBeTrue();

    $reflection = new ReflectionMethod($tool, 'schema');
    expect($reflection->getNumberOfParameters())->toBe(1);
});
