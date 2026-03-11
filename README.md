# Filament Copilot

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eslam-reda-div/filament-copilot.svg?style=flat-square)](https://packagist.org/packages/eslam-reda-div/filament-copilot)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**An AI-powered copilot for Filament v5 admin panels.** Gives your users a chat assistant that can read, create, update, delete, search, filter, sort records, navigate pages, fill forms, execute actions, remember user preferences, export conversations, and much more — all while respecting your existing authorization policies.

Built on [Laravel AI SDK](https://github.com/laravel/ai) with **real-time SSE streaming**, **multi-provider support** (OpenAI, Anthropic, Gemini, Groq, xAI, DeepSeek, Mistral, Ollama), **audit logging**, **rate limiting**, **planning mode**, and a comprehensive **admin dashboard**.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Step 1: Install via Composer](#step-1-install-via-composer)
  - [Step 2: Run the Install Wizard](#step-2-run-the-install-wizard)
  - [Step 3: Register the Plugin](#step-3-register-the-plugin)
  - [Step 4: Add the Trait to Your User Model](#step-4-add-the-trait-to-your-user-model)
  - [Step 5: Run Migrations](#step-5-run-migrations)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Config File Reference](#config-file-reference)
- [Plugin Options (Fluent API)](#plugin-options-fluent-api)
- [Traits](#traits)
  - [HasCopilotChat](#hascopilotchat)
  - [HasCopilotContext](#hascopilotcontext)
  - [HasCopilotPageContext](#hascopilotpagecontext)
  - [HasCopilotWidgetContext](#hascopilotwidgetcontext)
- [Contracts (Interfaces)](#contracts-interfaces)
  - [DescribesForCopilot](#describesforcopilot)
  - [ProvidesTool](#providestool)
  - [ProvidesWidgetData](#provideswidgetdata)
- [AI Macros (Component-Level Control)](#ai-macros-component-level-control)
  - [Schema Components (Form Fields)](#schema-components-form-fields)
  - [Table Columns](#table-columns)
  - [Table Filters](#table-filters)
  - [Actions & Bulk Actions](#actions--bulk-actions)
  - [Infolist Entries](#infolist-entries)
  - [Widgets](#widgets-macros)
  - [The needToAsk Flag](#the-needtoask-flag)
- [Description Values](#description-values)
- [Built-in Tools (22 Tools)](#built-in-tools-22-tools)
  - [Record Management](#record-management)
  - [Form Interaction](#form-interaction)
  - [Navigation & Discovery](#navigation--discovery)
  - [Memory & Planning](#memory--planning)
  - [Utility](#utility)
- [Creating Custom Tools](#creating-custom-tools)
- [Global Tools](#global-tools)
- [Streaming (SSE)](#streaming-sse)
- [Planning Mode](#planning-mode)
- [Agent Memory](#agent-memory)
- [Rate Limiting](#rate-limiting)
- [Audit Logging](#audit-logging)
- [Token Usage Tracking](#token-usage-tracking)
- [Conversation Export](#conversation-export)
- [Quick Actions](#quick-actions)
- [Authorization](#authorization)
- [Management Dashboard](#management-dashboard)
- [Multi-Tenancy Support](#multi-tenancy-support)
- [Custom System Prompt](#custom-system-prompt)
- [Events](#events)
- [Translations / Localization](#translations--localization)
- [Models Reference](#models-reference)
- [Architecture Overview](#architecture-overview)
- [Testing](#testing)
- [License](#license)

---

## Features

- **AI Chat Panel** — A floating chat window injected into every Filament page via render hooks
- **Real-time SSE Streaming** — Responses stream token-by-token in real time (not waiting for the full response)
- **22 Built-in Tools** — The AI agent can interact with your Filament resources, forms, tables, widgets, infolists, and actions
- **Multi-Provider Support** — OpenAI, Anthropic, Google Gemini, Groq, xAI, DeepSeek, Mistral, Ollama
- **Authorization Awareness** — Respects all Filament policies (`canViewAny`, `canCreate`, `canEdit`, `canDelete`)
- **Component-Level AI Control** — Macros like `->aiCanFill()`, `->aiCanRead()`, `->aiDescription()`, `->needToAsk()` on any Filament component
- **Planning Mode** — The agent can propose multi-step plans and wait for user approval before executing
- **Agent Memory** — The agent remembers user preferences across conversations
- **Audit Logging** — Complete audit trail of every AI action (messages, tool calls, record access, navigation)
- **Rate Limiting** — Per-user, per-hour/day message and token limits with blocking support
- **Token Usage Tracking** — Daily token usage records per user, per model, per panel
- **Conversation History** — Full conversation management with sidebar, load, delete, and auto-title
- **Conversation Export** — Export conversations to Markdown
- **Quick Actions** — Pre-defined prompts for common tasks
- **Admin Dashboard** — Stats overview, token usage charts, top users table, audit log viewer, conversation manager, rate limit manager
- **Multi-Tenancy** — Full tenant scoping for conversations, memories, audit logs, rate limits, and token usage
- **Translations** — Fully translatable with English included out of the box
- **Middleware Pipeline** — Rate limiting and audit logging applied via Laravel AI SDK agent middleware

---

## Requirements

| Dependency                    | Version |
| ----------------------------- | ------- |
| PHP                           | ^8.2    |
| Laravel                       | ^12.0   |
| Filament                      | ^5.0    |
| Laravel AI SDK (`laravel/ai`) | ^0.2.7  |

---

## Installation

### Step 1: Install via Composer

```bash
composer require eslam-reda-div/filament-copilot
```

### Step 2: Run the Install Wizard

The interactive installer will guide you through the entire setup:

```bash
php artisan filament-copilot:install
```

This wizard will:

1. Publish the config file (`config/filament-copilot.php`)
2. Publish and optionally run database migrations
3. Publish the Laravel AI SDK config (`config/ai.php`)
4. Let you choose your AI provider (OpenAI, Anthropic, Gemini, Groq, xAI, DeepSeek, Mistral, Ollama)
5. Let you choose your AI model
6. Configure your API key in `.env`
7. Display a summary of everything configured

You can re-run with `--force` to overwrite existing config:

```bash
php artisan filament-copilot:install --force
```

### Step 3: Register the Plugin

Add the plugin to your Filament panel provider:

```php
use EslamRedaDiv\FilamentCopilot\FilamentCopilotPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... your other configurations
        ->plugin(FilamentCopilotPlugin::make());
}
```

### Step 4: Add the Trait to Your User Model

Add the `HasCopilotChat` trait to your authenticatable model (usually `User`):

```php
use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotChat;

class User extends Authenticatable
{
    use HasCopilotChat;

    // ...
}
```

This gives each user a `copilotConversations()` morph-many relationship.

### Step 5: Run Migrations

If you didn't run migrations during the install wizard:

```bash
php artisan migrate
```

This creates 8 tables:

| Table                    | Purpose                                             |
| ------------------------ | --------------------------------------------------- |
| `copilot_conversations`  | Chat sessions linked to users, panels, and tenants  |
| `copilot_messages`       | Individual messages (user, assistant, system, tool) |
| `copilot_tool_calls`     | Records of tool invocations by the AI agent         |
| `copilot_plans`          | Multi-step plans proposed by the agent              |
| `copilot_audit_logs`     | Complete audit trail of all AI actions              |
| `copilot_rate_limits`    | Per-user rate limit configurations                  |
| `copilot_token_usages`   | Daily token usage tracking                          |
| `copilot_agent_memories` | Key-value memories the agent stores per user        |

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```dotenv
# Required: Your AI provider
COPILOT_PROVIDER=openai

# Required: The model to use
COPILOT_MODEL=gpt-4o

# Required: Your provider's API key (name depends on provider)
OPENAI_API_KEY=sk-...
# or ANTHROPIC_API_KEY=sk-ant-...
# or GEMINI_API_KEY=...
# or GROQ_API_KEY=...
# etc.
```

### Config File Reference

The full config is at `config/filament-copilot.php`:

```php
return [

    // AI Provider: openai, anthropic, gemini, groq, xai, deepseek, mistral, ollama
    'provider' => env('COPILOT_PROVIDER', 'openai'),

    // AI Model name
    'model' => env('COPILOT_MODEL'),

    // Agent behavior
    'agent' => [
        'should_think' => false,        // Enable "thinking" mode (extended reasoning)
        'should_plan' => false,          // Enable planning mode for complex tasks
        'should_approve_plan' => false,  // Require user approval before executing plans
        'max_steps' => 10,               // Maximum tool call iterations per request
        'temperature' => 0.3,            // Response randomness (0.0 = deterministic, 1.0 = creative)
        'max_tokens' => 4096,            // Maximum tokens in the response
        'timeout' => 120,                // Request timeout in seconds
    ],

    // Chat settings
    'chat' => [
        'enabled' => true,                    // Enable/disable the chat panel entirely
        'max_conversation_messages' => 50,    // Max messages loaded into context per conversation
        'title_auto_generate' => true,        // Auto-generate conversation titles from first message
    ],

    // SSE Streaming
    'streaming' => [
        'enabled' => true,     // Enable real-time streaming (recommended)
        'chunk_size' => 20,    // Chunk size for streaming
    ],

    // Rate limiting
    'rate_limits' => [
        'enabled' => false,              // Enable rate limiting
        'max_messages_per_hour' => 60,
        'max_messages_per_day' => 500,
        'max_tokens_per_hour' => 100000,
        'max_tokens_per_day' => 1000000,
    ],

    // Token budget warnings
    'token_budget' => [
        'enabled' => false,
        'warn_at_percentage' => 80,
        'daily_budget' => null,
        'monthly_budget' => null,
    ],

    // Audit logging
    'audit' => [
        'enabled' => true,
        'log_messages' => true,       // Log messages sent/received
        'log_tool_calls' => true,     // Log every tool call
        'log_plan_actions' => true,   // Log plan propose/approve/reject
        'log_record_access' => true,  // Log record reads/writes
        'log_navigation' => false,    // Log page navigation (disabled by default for noise reduction)
    ],

    // Agent memory
    'memory' => [
        'enabled' => true,
        'max_memories_per_user' => 100,  // Max key-value pairs stored per user
    ],

    // Authorization integration
    'respect_authorization' => true,  // When true, all tools respect Filament policies

    // Conversation export
    'export' => [
        'enabled' => true,
        'formats' => ['markdown'],  // Supported export formats
    ],

    // Management dashboard
    'management' => [
        'enabled' => false,    // Enable the admin management pages
        'guard' => null,       // Optional auth guard for management pages
    ],

    // Quick actions (pre-defined prompts)
    'quick_actions' => [],  // See "Quick Actions" section below

    // Custom system prompt (overrides the default)
    'system_prompt' => null,
];
```

---

## Plugin Options (Fluent API)

The plugin supports a fluent configuration API in your panel provider:

```php
use EslamRedaDiv\FilamentCopilot\FilamentCopilotPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            FilamentCopilotPlugin::make()

                // Enable/disable the chat panel
                ->chatEnabled(true)

                // Override the AI provider and model
                ->provider('openai')
                ->model('gpt-4o')

                // Agent behavior
                ->thinking(true)           // Enable extended reasoning
                ->planning(true)           // Enable planning mode
                ->shouldApprovePlan(true)  // Require plan approval
                ->maxSteps(15)             // Max tool iterations
                ->temperature(0.5)         // Response temperature

                // Conversation settings
                ->maxConversationMessages(100)

                // Custom system prompt
                ->systemPrompt('You are a helpful assistant for managing our inventory...')

                // Register global custom tools
                ->globalTools([
                    \App\Copilot\Tools\MyCustomTool::class,
                ])

                // Quick action buttons
                ->quickActions([
                    ['label' => 'Show stats', 'prompt' => 'Show me today\'s statistics'],
                    ['label' => 'Recent orders', 'prompt' => 'List the 5 most recent orders'],
                ])

                // Authorization callback
                ->authorizeUsing(fn ($user) => $user->hasRole('admin'))

                // Enable management dashboard
                ->managementEnabled(true)
                ->managementGuard('admin')
        );
}
```

### Available Fluent Methods

| Method                         | Type            | Description                                 |
| ------------------------------ | --------------- | ------------------------------------------- |
| `chatEnabled(bool)`            | `bool`          | Enable/disable the chat panel               |
| `provider(string)`             | `string`        | AI provider name                            |
| `model(string)`                | `string`        | AI model name                               |
| `thinking(bool)`               | `bool`          | Enable "thinking" / extended reasoning mode |
| `planning(bool)`               | `bool`          | Enable planning mode                        |
| `shouldApprovePlan(bool)`      | `bool`          | Require user approval for plans             |
| `maxSteps(int)`                | `int`           | Max tool-call iterations per request        |
| `temperature(float)`           | `float`         | Response temperature (0.0 – 1.0)            |
| `systemPrompt(?string)`        | `string\|null`  | Custom system prompt                        |
| `maxConversationMessages(int)` | `int`           | Max messages in conversation context        |
| `globalTools(array)`           | `array`         | Register additional custom tools            |
| `quickActions(array)`          | `array`         | Pre-defined prompt buttons                  |
| `authorizeUsing(?Closure)`     | `Closure\|null` | Authorization callback                      |
| `managementEnabled(bool)`      | `bool`          | Enable admin dashboard pages                |
| `managementGuard(?string)`     | `string\|null`  | Auth guard for management pages             |

---

## Traits

### HasCopilotChat

**Namespace:** `EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotChat`

**Use on:** Your User model (or any authenticatable model).

Provides: `copilotConversations()` — a morph-many relationship to `CopilotConversation`.

```php
use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotChat;

class User extends Authenticatable
{
    use HasCopilotChat;
}

// Usage:
$user->copilotConversations; // Collection of CopilotConversation
```

---

### HasCopilotContext

**Namespace:** `EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotContext`

**Use on:** Filament Resource classes to expose them to the AI agent with fine-grained control.

```php
use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotContext;

class PostResource extends Resource
{
    use HasCopilotContext;

    // Optional: Customize the AI description
    public function copilotResourceDescription(): ?string
    {
        return 'Manages blog posts with titles, content, categories, and publish status.';
    }

    // Optional: Describe a specific record for the AI
    public function copilotRecordDescription(Model $record): array
    {
        return [
            "Post #{$record->id}: {$record->title}",
            "Status: {$record->status}",
        ];
    }

    // Optional: Register custom tools specific to this resource
    public function copilotTools(): array
    {
        return [
            new \App\Copilot\Tools\PublishPostTool(),
        ];
    }

    // Optional: Restrict which fields the AI can read (null = all visible)
    public static function copilotReadableFields(): ?array
    {
        return ['title', 'content', 'status', 'category_id', 'created_at'];
    }

    // Optional: Restrict which fields the AI can write (null = all fillable)
    public static function copilotWritableFields(): ?array
    {
        return ['title', 'content', 'status', 'category_id'];
    }

    // Optional: Whether the AI can create records (default: true)
    public static function copilotCanCreate(): bool
    {
        return true;
    }

    // Optional: Whether the AI can delete records (default: false)
    public static function copilotCanDelete(): bool
    {
        return false;
    }
}
```

---

### HasCopilotPageContext

**Namespace:** `EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotPageContext`

**Use on:** Filament Page classes to expose them to the AI agent.

```php
use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotPageContext;

class Dashboard extends Page
{
    use HasCopilotPageContext;

    // Optional: Custom description for the AI
    public function copilotPageDescription(): ?string
    {
        return 'Main dashboard showing key metrics and charts.';
    }

    // Optional: Custom tools available on this page
    public function copilotTools(): array
    {
        return [];
    }
}
```

---

### HasCopilotWidgetContext

**Namespace:** `EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotWidgetContext`

**Use on:** Filament Widget classes to expose their data to the AI agent.

```php
use EslamRedaDiv\FilamentCopilot\Concerns\HasCopilotWidgetContext;

class StatsWidget extends Widget
{
    use HasCopilotWidgetContext;

    // Optional: Custom description
    public function copilotWidgetDescription(): ?string
    {
        return 'Displays total users, orders, and revenue.';
    }

    // Optional: Expose the widget's data to the AI
    public function copilotWidgetData(): array
    {
        return [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total'),
        ];
    }

    // Optional: Custom tools
    public function copilotTools(): array
    {
        return [];
    }
}
```

---

## Contracts (Interfaces)

### DescribesForCopilot

```php
use EslamRedaDiv\FilamentCopilot\Contracts\DescribesForCopilot;

class MyComponent implements DescribesForCopilot
{
    public function copilotDescription(): ?string
    {
        return 'Describes this component for the AI agent.';
    }
}
```

### ProvidesTool

```php
use EslamRedaDiv\FilamentCopilot\Contracts\ProvidesTool;

class MyResource implements ProvidesTool
{
    public function copilotTools(): array
    {
        return [new MyCustomTool()];
    }
}
```

### ProvidesWidgetData

```php
use EslamRedaDiv\FilamentCopilot\Contracts\ProvidesWidgetData;

class MyWidget implements ProvidesWidgetData
{
    public function copilotWidgetData(): array
    {
        return ['metric' => 42];
    }

    public function copilotWidgetDescription(): ?string
    {
        return 'Shows a key metric.';
    }
}
```

---

## AI Macros (Component-Level Control)

The package registers macros on all major Filament component types, giving you fine-grained control over what the AI agent can do with each component.

### Schema Components (Form Fields)

```php
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Select;

TextInput::make('title')
    ->aiDescription('The blog post title, should be descriptive and SEO-friendly')
    ->aiCanFill(true)             // The AI is allowed to fill this field
    ->aiCanSave(true)             // The AI is allowed to save this field
    ->aiCanDraft(true)            // The AI is allowed to draft a value
    ->aiCanRead(true);            // The AI is allowed to read this field's value

Select::make('category_id')
    ->aiDescription('The category this post belongs to')
    ->aiCanFill(true, needToAsk: true);  // AI must ask the user before filling this
```

### Table Columns

```php
use Filament\Tables\Columns\TextColumn;

TextColumn::make('email')
    ->aiDescription('The user email address')
    ->aiCanRead(true)             // AI can read this column's value
    ->aiCanSearch(true)           // AI can search by this column
    ->aiCanSort(true);            // AI can sort by this column
```

### Table Filters

```php
use Filament\Tables\Filters\SelectFilter;

SelectFilter::make('status')
    ->aiDescription('Filter posts by their publication status')
    ->aiCanUse(true);             // AI can apply this filter
```

### Actions & Bulk Actions

```php
use Filament\Actions\Action;
use Filament\Actions\BulkAction;

Action::make('publish')
    ->aiDescription('Publish the post, making it visible to all users')
    ->aiCanExecute(true);         // AI can trigger this action

BulkAction::make('archive')
    ->aiDescription('Archive selected posts')
    ->aiCanExecute(true, needToAsk: true);  // AI must confirm with user first
```

### Infolist Entries

```php
use Filament\Infolists\Components\TextEntry;

TextEntry::make('bio')
    ->aiDescription('The user biography displayed on their profile')
    ->aiCanRead(true);
```

### Widgets (Macros)

```php
use Filament\Widgets\StatsOverviewWidget;

// Inside boot() or a method:
$widget->aiDescription('Revenue dashboard widget')
    ->aiCanInteract(true);
```

### The `needToAsk` Flag

Any capability macro accepts a `needToAsk` parameter. When set to `true`, the AI agent is instructed:

> "You MUST ask the user for the value of this field before filling it. Never assume or auto-fill."

This is critical for sensitive fields like passwords, financial amounts, or irreversible actions.

```php
TextInput::make('password')
    ->aiCanFill(true, needToAsk: true);

Select::make('payment_method')
    ->aiCanFill(true, needToAsk: true);

Action::make('delete_account')
    ->aiCanExecute(true, needToAsk: true);
```

---

## Description Values

Use description value objects to provide rich, typed metadata about your fields for the AI agent when implementing `copilotRecordDescription()` or custom tool responses:

```php
use EslamRedaDiv\FilamentCopilot\Descriptions\TextValue;
use EslamRedaDiv\FilamentCopilot\Descriptions\NumericValue;
use EslamRedaDiv\FilamentCopilot\Descriptions\BooleanValue;
use EslamRedaDiv\FilamentCopilot\Descriptions\DateValue;
use EslamRedaDiv\FilamentCopilot\Descriptions\ListValue;
use EslamRedaDiv\FilamentCopilot\Descriptions\RelationValue;
```

### TextValue

```php
TextValue::make('Title')
    ->value('My Blog Post')
    ->description('The main heading of the blog post')
    ->required()
    ->maxLength(255)
    ->format('plain');     // 'plain', 'html', 'markdown'
```

### NumericValue

```php
NumericValue::make('Price')
    ->value(29.99)
    ->description('Product price in USD')
    ->required()
    ->min(0)
    ->max(9999.99)
    ->unit('USD');
```

### BooleanValue

```php
BooleanValue::make('Is Published')
    ->value(true)
    ->trueLabel('Published')
    ->falseLabel('Draft');
```

### DateValue

```php
DateValue::make('Created At')
    ->value('2024-01-15')
    ->format('Y-m-d')
    ->withTime(false);

DateValue::make('Expires At')
    ->value('2024-06-15 14:30:00')
    ->format('Y-m-d H:i:s')
    ->withTime(true);
```

### ListValue

```php
ListValue::make('Status')
    ->value('active')
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ]);
```

### RelationValue

```php
RelationValue::make('Author')
    ->value(42)
    ->relatedModel(\App\Models\User::class)
    ->displayField('name');
```

---

## Built-in Tools (22 Tools)

The package ships with 22 built-in tools that the AI agent can use. All tools automatically respect authorization policies, log audit events, and are scoped to the current panel and tenant.

### Record Management

| Tool                | Description                                    |
| ------------------- | ---------------------------------------------- |
| `ListRecordsTool`   | List paginated records from any resource table |
| `GetRecordTool`     | Get a specific record by ID with full details  |
| `SearchRecordsTool` | Search records by a query string               |
| `FilterRecordsTool` | Apply table filters to a resource              |
| `SortRecordsTool`   | Sort records by a column                       |
| `CreateRecordTool`  | Create a new record with provided field values |
| `UpdateRecordTool`  | Update an existing record's fields             |
| `DeleteRecordTool`  | Delete a record (requires authorization)       |

### Form Interaction

| Tool                | Description                                                   |
| ------------------- | ------------------------------------------------------------- |
| `FillFormTool`      | Fill form fields with specified values                        |
| `GetFormDataTool`   | Read current form field values                                |
| `GetSchemaInfoTool` | Get the full schema (columns, types, fillable) for a resource |
| `ReadInfolistTool`  | Read data from an infolist view page                          |

### Navigation & Discovery

| Tool                 | Description                                                                                   |
| -------------------- | --------------------------------------------------------------------------------------------- |
| `NavigateToPageTool` | Navigate to a page, resource list, create, view, or edit page. Returns the URL.               |
| `GetCurrentPageTool` | Get the current page the user is viewing                                                      |
| `ListResourcesTool`  | List all available resources with their slugs and labels                                      |
| `ListWidgetsTool`    | List all registered widgets                                                                   |
| `GetWidgetDataTool`  | Get data from a widget that implements `ProvidesWidgetData` or uses `HasCopilotWidgetContext` |
| `ExecuteActionTool`  | Execute a Filament action                                                                     |

### Memory & Planning

| Tool             | Description                                                                      |
| ---------------- | -------------------------------------------------------------------------------- |
| `RememberTool`   | Store a key-value memory about the user (e.g., "preferred_language" = "English") |
| `RecallTool`     | Retrieve a stored memory by key                                                  |
| `CreatePlanTool` | Create a multi-step plan for complex tasks                                       |

### Utility

| Tool                     | Description                                                           |
| ------------------------ | --------------------------------------------------------------------- |
| `AskUserTool`            | Ask the user a question and wait for their response before continuing |
| `ExportConversationTool` | Export the current conversation to Markdown                           |

---

## Creating Custom Tools

Extend `BaseTool` to create your own tools that integrate with the copilot's authorization, audit logging, and panel/tenant scoping:

```php
<?php

namespace App\Copilot\Tools;

use EslamRedaDiv\FilamentCopilot\Enums\AuditAction;
use EslamRedaDiv\FilamentCopilot\Tools\BaseTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class PublishPostTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Publish a draft blog post, making it visible to all users.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->integer()
                ->description('The ID of the post to publish')
                ->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $postId = $request['post_id'];

        $post = \App\Models\Post::find($postId);

        if (!$post) {
            return "Post #{$postId} not found.";
        }

        // Use built-in authorization checking
        $resourceClass = \App\Filament\Resources\PostResource::class;
        if (!$this->authorizeEdit($resourceClass, $post)) {
            return 'You are not authorized to publish this post.';
        }

        $post->update(['status' => 'published', 'published_at' => now()]);

        // Log the action via built-in audit
        $this->audit(AuditAction::ActionExecuted, $resourceClass, (string) $post->id, [
            'action' => 'publish',
        ]);

        return "Post #{$post->id} '{$post->title}' has been published successfully.";
    }
}
```

### `BaseTool` Provides

| Property/Method                                                   | Description                            |
| ----------------------------------------------------------------- | -------------------------------------- |
| `$this->panelId`                                                  | Current Filament panel ID              |
| `$this->user`                                                     | Authenticated user model               |
| `$this->tenant`                                                   | Current tenant model (or null)         |
| `$this->authorizeViewAny($resourceClass)`                         | Check viewAny policy                   |
| `$this->authorizeView($resourceClass, $record)`                   | Check view policy                      |
| `$this->authorizeCreate($resourceClass)`                          | Check create policy                    |
| `$this->authorizeEdit($resourceClass, $record)`                   | Check edit policy                      |
| `$this->authorizeDelete($resourceClass, $record)`                 | Check delete policy                    |
| `$this->resolveResource($slug)`                                   | Resolve a resource class from its slug |
| `$this->audit($action, $resource, $recordKey, $payload)`          | Log an audit event                     |
| `$this->dispatchToolExecuted($name, $result, $messageId, $input)` | Dispatch tool executed event           |

---

## Global Tools

Register custom tools globally so they're available in every conversation:

**Via plugin:**

```php
FilamentCopilotPlugin::make()
    ->globalTools([
        \App\Copilot\Tools\PublishPostTool::class,
        \App\Copilot\Tools\SendNotificationTool::class,
    ])
```

**Via ToolRegistry service:**

```php
use EslamRedaDiv\FilamentCopilot\Services\ToolRegistry;

// In a service provider:
app(ToolRegistry::class)->registerGlobal(\App\Copilot\Tools\MyTool::class);
```

---

## Streaming (SSE)

When streaming is enabled (default), responses are delivered in real-time via **Server-Sent Events**. The backend uses the Laravel AI SDK's `$agent->stream()` method which returns a `StreamableAgentResponse` that iterates `TextDelta` events — delivering tokens as they're generated.

**SSE Event Types:**

| Event          | Payload                                          | Description                      |
| -------------- | ------------------------------------------------ | -------------------------------- |
| `conversation` | `{id: string}`                                   | The conversation ID (sent first) |
| `start`        | `{}`                                             | Streaming has begun              |
| `chunk`        | `{text: string}`                                 | A text chunk from the AI         |
| `usage`        | `{input_tokens, output_tokens}`                  | Token usage summary              |
| `plan_status`  | `{id, status, current_step, total_steps, steps}` | Plan status update               |
| `error`        | `{message: string}`                              | An error occurred                |
| `done`         | `{}`                                             | Streaming complete               |

**The SSE endpoint:** `POST /copilot/stream`

To disable streaming and use synchronous mode:

```php
// config/filament-copilot.php
'streaming' => [
    'enabled' => false,
],
```

---

## Planning Mode

When enabled, the AI agent can create multi-step plans for complex tasks and wait for user approval before executing them.

```php
// config/filament-copilot.php
'agent' => [
    'should_plan' => true,
    'should_approve_plan' => true,
],

// Or via plugin:
FilamentCopilotPlugin::make()
    ->planning(true)
    ->shouldApprovePlan(true)
```

**How it works:**

1. User asks: "Create 5 sample blog posts with different categories"
2. The agent uses `CreatePlanTool` to propose a plan with step-by-step descriptions
3. A plan notification appears in the chat with Approve / Reject buttons
4. If approved, the agent executes each step sequentially
5. Progress is reported via `plan_status` SSE events

**Plan Statuses:** `proposed` → `approved` → `executing` → `completed` (or `rejected` / `failed`)

---

## Agent Memory

The AI agent can remember things about each user across conversations. Memories are scoped per user, per panel, and per tenant.

```php
// config/filament-copilot.php
'memory' => [
    'enabled' => true,
    'max_memories_per_user' => 100,
],
```

**Example conversation:**

> **User:** "I prefer to see dates in DD/MM/YYYY format"
>
> **Copilot:** _Remembered: date_format = DD/MM/YYYY_

In future conversations, the agent's system prompt includes:

```
## Your Memories About This User
- date_format: DD/MM/YYYY
```

The agent uses `RememberTool` and `RecallTool` automatically.

---

## Rate Limiting

Protect your API costs with per-user rate limits on message count and token usage:

```php
// config/filament-copilot.php
'rate_limits' => [
    'enabled' => true,
    'max_messages_per_hour' => 60,
    'max_messages_per_day' => 500,
    'max_tokens_per_hour' => 100000,
    'max_tokens_per_day' => 1000000,
],
```

**Per-user overrides** can be configured via the management dashboard or programmatically:

```php
use EslamRedaDiv\FilamentCopilot\Services\RateLimitService;

$service = app(RateLimitService::class);

// Block a user
$service->blockUser($user, 'admin', 'Violated usage policy', now()->addDays(7));

// Unblock a user
$service->unblockUser($user, 'admin');

// Check remaining messages
$remaining = $service->getRemainingMessages($user, 'admin');
```

Rate limiting is enforced at two levels:

1. **SSE Controller** — Checks before processing the request
2. **Agent Middleware** — `RateLimitMiddleware` applies during the AI pipeline

---

## Audit Logging

Every AI action is logged with full context including the user, panel, tenant, IP address, resource type, record key, and action-specific payload.

```php
// config/filament-copilot.php
'audit' => [
    'enabled' => true,
    'log_messages' => true,
    'log_tool_calls' => true,
    'log_plan_actions' => true,
    'log_record_access' => true,
    'log_navigation' => false,
],
```

**Audit Actions Tracked:**

| Action                                                               | Description                     |
| -------------------------------------------------------------------- | ------------------------------- |
| `message_sent`                                                       | User sent a message             |
| `message_received` / `response_received`                             | AI responded                    |
| `tool_called` / `tool_executed`                                      | A tool was invoked              |
| `record_read` / `record_searched`                                    | Records were accessed           |
| `record_created` / `record_updated` / `record_deleted`               | Data modifications              |
| `form_filled` / `form_saved`                                         | Form interactions               |
| `action_executed`                                                    | A Filament action was triggered |
| `filter_applied` / `record_sorted` / `record_filtered`               | Table interactions              |
| `plan_created` / `plan_approved` / `plan_rejected` / `plan_executed` | Planning events                 |
| `navigated_to`                                                       | Page navigation                 |
| `rate_limit_hit`                                                     | Rate limit was exceeded         |
| `conversation_started` / `conversation_exported`                     | Conversation events             |
| `approval_requested` / `approval_granted` / `approval_denied`        | Approval flow                   |

---

## Token Usage Tracking

Token usage is recorded daily per user, per panel, per model, per provider:

```php
use EslamRedaDiv\FilamentCopilot\Models\CopilotTokenUsage;

// Get today's usage for a user
$todayUsage = CopilotTokenUsage::forPanel('admin')
    ->forParticipant($user)
    ->forToday()
    ->sum('total_tokens');

// Get this month's usage
$monthUsage = CopilotTokenUsage::forPanel('admin')
    ->forParticipant($user)
    ->whereBetween('usage_date', [now()->startOfMonth(), now()->endOfMonth()])
    ->sum('total_tokens');
```

---

## Conversation Export

Conversations can be exported to Markdown:

```php
use EslamRedaDiv\FilamentCopilot\Services\ExportService;

$exportService = app(ExportService::class);
$markdown = $exportService->toMarkdown($conversationId);
```

Users can also click the **Export** button in the chat UI. The export includes:

- Conversation title and date
- Panel ID
- All messages with roles
- Total token usage

---

## Quick Actions

Pre-defined prompt buttons appear above the chat input:

```php
// config/filament-copilot.php
'quick_actions' => [
    ['label' => 'Show stats', 'prompt' => 'Show me an overview of today\'s activity'],
    ['label' => 'Recent orders', 'prompt' => 'List the 10 most recent orders'],
    ['label' => 'Low stock', 'prompt' => 'Show products with less than 5 items in stock'],
],

// Or via plugin:
FilamentCopilotPlugin::make()
    ->quickActions([
        ['label' => 'Show stats', 'prompt' => 'Show me today\'s statistics'],
    ])
```

---

## Authorization

By default, all tools respect Filament's authorization policies. When a user asks the AI to list, view, create, edit, or delete records, the tool checks the resource's `canViewAny()`, `canView()`, `canCreate()`, `canEdit()`, and `canDelete()` methods.

```php
// config/filament-copilot.php
'respect_authorization' => true,  // Set to false to disable (not recommended)
```

**Custom authorization** at the plugin level:

```php
FilamentCopilotPlugin::make()
    ->authorizeUsing(fn ($user) => $user->hasRole('admin'));
```

**Resource-level control** via `HasCopilotContext`:

```php
// Only allow reading specific fields
public static function copilotReadableFields(): ?array
{
    return ['title', 'status', 'created_at']; // null = all fields
}

// Only allow writing specific fields
public static function copilotWritableFields(): ?array
{
    return ['title', 'status']; // null = all fillable fields
}

// Disable AI deletion for this resource
public static function copilotCanDelete(): bool
{
    return false;
}
```

---

## Management Dashboard

Enable the admin dashboard to monitor and manage copilot usage:

```php
FilamentCopilotPlugin::make()
    ->managementEnabled(true)
    ->managementGuard('admin')  // Optional: restrict to specific auth guard
```

This registers the following in your Filament panel:

### Pages

- **Copilot Dashboard** — Overview with stats widgets

### Resources

- **Conversations** — Browse and manage all conversations
- **Audit Logs** — Search and filter all audit events
- **Rate Limits** — View and configure per-user rate limits, block/unblock users

### Dashboard Widgets

- **CopilotStatsOverview** — Total conversations, tokens today, tokens this month, audit events today
- **TokenUsageChart** — Token usage over the last 30 days
- **TopUsersTable** — Top users by token consumption

---

## Multi-Tenancy Support

The package has full multi-tenancy support. All data is scoped by tenant:

- **Conversations** — scoped to the current tenant
- **Messages** — scoped via conversation
- **Audit Logs** — include tenant context
- **Rate Limits** — per-user per-tenant
- **Token Usage** — per-user per-tenant
- **Agent Memory** — per-user per-tenant
- **Plans** — scoped via conversation

No additional configuration is needed — the package automatically picks up `Filament::getTenant()` and scopes all queries accordingly.

---

## Custom System Prompt

Override the default system prompt entirely:

```php
// config/filament-copilot.php
'system_prompt' => 'You are an inventory management assistant. Help users track stock, process orders, and generate reports. Always be concise.',

// Or via plugin:
FilamentCopilotPlugin::make()
    ->systemPrompt('You are a helpful HR assistant...')
```

The default system prompt includes:

- General assistant guidelines
- Authorization instructions
- Auto-discovered resource context (models, columns, fillable fields, casts)
- Auto-discovered page context
- Auto-discovered widget context
- User memories
- Planning instructions (when enabled)
- `needToAsk` field instructions

---

## Events

The package dispatches events you can listen to:

| Event                         | Payload                                                | Description                    |
| ----------------------------- | ------------------------------------------------------ | ------------------------------ |
| `CopilotConversationCreated`  | `$conversation`                                        | A new conversation was started |
| `CopilotMessageSent`          | `$conversation, $content, $panelId`                    | A user message was sent        |
| `CopilotResponseReceived`     | `$conversation, $message, $inputTokens, $outputTokens` | The AI responded               |
| `CopilotToolExecuted`         | `$toolCall, $toolName, $result`                        | A tool was executed            |
| `CopilotToolApprovalRequired` | —                                                      | A tool requires user approval  |
| `CopilotPlanProposed`         | `$plan, $conversation`                                 | A plan was proposed            |
| `CopilotPlanApproved`         | `$plan, $conversation`                                 | A plan was approved            |
| `CopilotPlanRejected`         | `$plan, $conversation, $reason`                        | A plan was rejected            |
| `CopilotRateLimitExceeded`    | `$user, $panelId, $tenant`                             | A user hit the rate limit      |

**Example listener:**

```php
// In EventServiceProvider or listener class:
use EslamRedaDiv\FilamentCopilot\Events\CopilotResponseReceived;

Event::listen(CopilotResponseReceived::class, function ($event) {
    Log::info('Copilot responded', [
        'conversation' => $event->conversation->id,
        'tokens' => $event->inputTokens + $event->outputTokens,
    ]);
});
```

---

## Translations / Localization

The package ships with English translations and is fully translatable. Publish the translations:

```bash
php artisan vendor:publish --tag=filament-copilot-translations
```

This creates `lang/vendor/filament-copilot/en/filament-copilot.php`. Create additional language files (e.g., `ar/filament-copilot.php`) for other locales.

Key translation strings include:

- Chat UI labels (send, close, input placeholder, welcome message)
- Plan notifications (approve, reject)
- Error messages (rate limit exceeded, error occurred)
- Dashboard and resource labels
- Table column names

---

## Models Reference

| Model                 | Description                                                                       |
| --------------------- | --------------------------------------------------------------------------------- |
| `CopilotConversation` | Chat session with participant, panel, tenant, title, metadata. Uses ULIDs.        |
| `CopilotMessage`      | Individual message with role (user/assistant/system/tool), content, token counts. |
| `CopilotToolCall`     | Tool execution record with name, input, output, status.                           |
| `CopilotPlan`         | Multi-step plan with steps array, status, current step counter.                   |
| `CopilotAuditLog`     | Audit event with action, resource type, record key, payload, IP address.          |
| `CopilotRateLimit`    | Per-user rate limit config with message/token limits and blocking.                |
| `CopilotTokenUsage`   | Daily token usage with input/output/total tokens, model, provider.                |
| `CopilotAgentMemory`  | Key-value memory store per user per panel per tenant.                             |

All models use **ULIDs** as primary keys and support **morph-to** relationships for participants and tenants.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                  Filament Panel (Browser)                │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │ CopilotChat  │  │CopilotButton │  │ConversationSB │  │
│  │  (Livewire)  │  │  (Livewire)  │  │  (Livewire)   │  │
│  └──────┬───────┘  └──────────────┘  └───────────────┘  │
│         │ SSE (fetch + ReadableStream)                   │
└─────────┼───────────────────────────────────────────────┘
          │ POST /copilot/stream
          ▼
┌─────────────────────────────────────────────────────────┐
│               StreamController (Backend)                 │
│  ┌─────────────────────────────────────────────┐        │
│  │ CopilotAgent (Laravel AI SDK Agent)         │        │
│  │  ├── ContextBuilder                          │        │
│  │  │    ├── ResourceInspector → SchemaInspector│        │
│  │  │    ├── PageInspector                      │        │
│  │  │    ├── WidgetInspector                    │        │
│  │  │    └── MemoryContext                      │        │
│  │  ├── ToolRegistry (22 built-in + custom)    │        │
│  │  ├── PlanningEngine                          │        │
│  │  └── Middleware Pipeline                     │        │
│  │       ├── RateLimitMiddleware                │        │
│  │       └── AuditMiddleware                    │        │
│  └─────────────────────────────────────────────┘        │
│  ┌──────────────────────┐  ┌──────────────────┐        │
│  │ ConversationManager   │  │ RateLimitService │        │
│  └──────────────────────┘  └──────────────────┘        │
│  ┌──────────────────────┐  ┌──────────────────┐        │
│  │ ExportService         │  │ PlanningEngine   │        │
│  └──────────────────────┘  └──────────────────┘        │
└─────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────┐
│                   AI Provider API                        │
│  OpenAI │ Anthropic │ Gemini │ Groq │ xAI │ DeepSeek   │
│  Mistral │ Ollama                                       │
└─────────────────────────────────────────────────────────┘
```

---

## Testing

```bash
composer test
```

The package includes 110 tests with 236 assertions covering:

- Agent configuration and context building
- Tool authorization and audit logging
- Conversation management
- Rate limiting and token tracking
- Planning engine workflow
- Event dispatching
- Macro registration
- Model relationships and scopes
- SSE streaming

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**If you find this package useful, please consider giving it a ⭐ on [GitHub](https://github.com/eslam-reda-div/filament-copilot)!**
