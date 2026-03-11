<?php

use EslamRedaDiv\FilamentCopilot\Services\ToolRegistry;

it('builds default tools', function () {
    $user = createTestUser();
    $registry = app(ToolRegistry::class);

    $tools = $registry->buildTools('admin', $user);

    // Should have the 24 built-in tools
    expect($tools)->toBeArray()
        ->and(count($tools))->toBeGreaterThanOrEqual(24);
});

it('accepts global custom tools', function () {
    $registry = app(ToolRegistry::class);

    $registry->registerGlobal('App\\Tools\\CustomTool');

    expect(count($registry->getToolClasses()))->toBe(25);
});
