@php
    $isUser = ($msg['role'] ?? '') === 'user';
    $isSystem = ($msg['role'] ?? '') === 'system';
    $isAssistant = ($msg['role'] ?? '') === 'assistant';
    $isTool = ($msg['role'] ?? '') === 'tool';
    $isThinking = ($msg['role'] ?? '') === 'thinking';
    $isToolCall = ($msg['role'] ?? '') === 'tool_call';
    $isToolResult = ($msg['role'] ?? '') === 'tool_result';
@endphp

@if ($isUser)
    <div class="flex items-start gap-2.5 justify-end">
        <div class="min-w-0 max-w-[85%] rounded-2xl rounded-tr-md px-3.5 py-2.5 bg-primary-600 text-white">
            <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">{{ $msg['content'] }}</p>
        </div>
        <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shrink-0 mt-0.5">
            <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-gray-600 dark:text-gray-300" />
        </div>
    </div>
@elseif($isThinking)
    {{-- Collapsible thinking box (DeepSeek/ChatGPT style) --}}
    <div class="flex items-start gap-2.5">
        <div
            class="w-7 h-7 rounded-full bg-info-100 dark:bg-info-900/30 flex items-center justify-center shrink-0 mt-0.5">
            <x-filament::icon icon="heroicon-o-light-bulb" class="w-4 h-4 text-info-600 dark:text-info-400" />
        </div>
        <div class="min-w-0 max-w-[85%] w-full" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                class="flex items-center gap-2 px-3 py-2 w-full rounded-t-xl bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 hover:bg-info-100 dark:hover:bg-info-900/30 transition-colors"
                :class="{ 'rounded-b-xl': !open }">
                <svg class="w-3.5 h-3.5 text-info-500 transition-transform duration-200" :class="{ 'rotate-90': open }"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-xs font-medium text-info-700 dark:text-info-300">Thinking</span>
            </button>
            <div x-show="open" x-collapse
                class="px-3 py-2 bg-info-50/50 dark:bg-info-900/10 border border-t-0 border-info-200 dark:border-info-800 rounded-b-xl">
                <p
                    class="text-xs text-info-700 dark:text-info-300 whitespace-pre-wrap break-words font-mono leading-relaxed max-h-40 overflow-y-auto">
                    {{ $msg['content'] }}</p>
            </div>
        </div>
    </div>
@elseif($isToolCall || $isToolResult || $isTool)
    {{-- Collapsible tool call box --}}
    @php
        $toolName = $msg['tool_name'] ?? ($msg['name'] ?? 'Tool');
        $isSuccess = $msg['success'] ?? true;
        $hasResult = !empty($msg['result']);
        $hasError = !empty($msg['error']);
    @endphp
    <div class="flex items-start gap-2.5">
        <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0 mt-0.5">
            <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-4 h-4 text-gray-500" />
        </div>
        <div class="min-w-0 max-w-[85%] w-full" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                class="flex items-center gap-2 px-3 py-2 w-full rounded-t-xl border transition-colors {{ $hasError ? 'bg-danger-50 dark:bg-danger-900/10 border-danger-200 dark:border-danger-800 hover:bg-danger-100 dark:hover:bg-danger-900/20' : 'bg-success-50 dark:bg-success-900/10 border-success-200 dark:border-success-800 hover:bg-success-100 dark:hover:bg-success-900/20' }}"
                :class="{ 'rounded-b-xl': !open }">
                <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-90': open }"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                @if ($hasError)
                    <x-filament::icon icon="heroicon-o-x-circle" class="w-3.5 h-3.5 text-danger-500" />
                @else
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-3.5 h-3.5 text-success-500" />
                @endif
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ $toolName }}</span>
            </button>
            <div x-show="open" x-collapse
                class="px-3 py-2 border border-t-0 rounded-b-xl {{ $hasError ? 'bg-danger-50/50 dark:bg-danger-900/5 border-danger-200 dark:border-danger-800' : 'bg-success-50/50 dark:bg-success-900/5 border-success-200 dark:border-success-800' }}">
                @if (!empty($msg['arguments']))
                    <div class="mb-1">
                        <span
                            class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Arguments</span>
                        <pre
                            class="text-xs text-gray-600 dark:text-gray-400 font-mono whitespace-pre-wrap break-all mt-0.5 max-h-24 overflow-y-auto">{{ is_string($msg['arguments']) ? $msg['arguments'] : json_encode($msg['arguments'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
                @if ($hasResult || !empty($msg['content']))
                    <div>
                        <span
                            class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Result</span>
                        <pre
                            class="text-xs text-gray-600 dark:text-gray-400 font-mono whitespace-pre-wrap break-all mt-0.5 max-h-24 overflow-y-auto">{{ $msg['result'] ?? $msg['content'] }}</pre>
                    </div>
                @endif
                @if ($hasError)
                    <div>
                        <span class="text-[10px] font-semibold text-danger-500 uppercase tracking-wider">Error</span>
                        <p class="text-xs text-danger-600 dark:text-danger-400 mt-0.5">{{ $msg['error'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@elseif($isAssistant)
    <div class="flex items-start gap-2.5">
        <div
            class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0 mt-0.5">
            <x-filament::icon icon="heroicon-o-sparkles" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
        </div>
        <div
            class="min-w-0 max-w-[85%] rounded-2xl rounded-tl-md px-3.5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            <div
                class="text-sm leading-relaxed prose prose-sm dark:prose-invert max-w-none break-words [&>*:first-child]:mt-0 [&>*:last-child]:mb-0">
                {!! \Illuminate\Support\Str::markdown($msg['content'] ?? '') !!}
            </div>
        </div>
    </div>
@elseif($isSystem)
    <div class="flex justify-center px-4">
        <div
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-300 max-w-full">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-3.5 h-3.5 shrink-0" />
            <span class="text-xs break-words">{{ $msg['content'] }}</span>
        </div>
    </div>
@endif
