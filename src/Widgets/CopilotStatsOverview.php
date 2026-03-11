<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Widgets;

use EslamRedaDiv\FilamentCopilot\Models\CopilotAuditLog;
use EslamRedaDiv\FilamentCopilot\Models\CopilotConversation;
use EslamRedaDiv\FilamentCopilot\Models\CopilotTokenUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CopilotStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        return [
            Stat::make(
                __('filament-copilot::filament-copilot.total_conversations'),
                CopilotConversation::count(),
            )->icon('heroicon-o-chat-bubble-left-right'),

            Stat::make(
                __('filament-copilot::filament-copilot.tokens_today'),
                number_format(
                    (int) CopilotTokenUsage::where('usage_date', $today)->sum('total_tokens')
                ),
            )->icon('heroicon-o-calculator'),

            Stat::make(
                __('filament-copilot::filament-copilot.tokens_this_month'),
                number_format(
                    (int) CopilotTokenUsage::where('usage_date', '>=', now()->startOfMonth()->toDateString())->sum('total_tokens')
                ),
            )->icon('heroicon-o-chart-bar'),

            Stat::make(
                __('filament-copilot::filament-copilot.audit_events_today'),
                CopilotAuditLog::where('created_at', '>=', now()->startOfDay())->count(),
            )->icon('heroicon-o-shield-check'),
        ];
    }
}
