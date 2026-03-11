<?php

declare(strict_types=1);

namespace EslamRedaDiv\FilamentCopilot\Macros;

use EslamRedaDiv\FilamentCopilot\Enums\CapabilityType;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Widgets\Widget;

class MacroRegistrar
{
    protected static array $componentCapabilities = [];

    protected static array $componentDescriptions = [];

    protected static array $componentNeedToAsk = [];

    /** @var array<string> Field names globally tracked with needToAsk=true */
    protected static array $needToAskFieldNames = [];

    public function register(): void
    {
        if (class_exists(SchemaComponent::class)) {
            $this->registerSchemaComponentMacros();
        }

        if (class_exists(Column::class)) {
            $this->registerColumnMacros();
        }

        if (class_exists(BaseFilter::class)) {
            $this->registerFilterMacros();
        }

        if (class_exists(Action::class)) {
            $this->registerActionMacros();
        }

        if (class_exists(BulkAction::class)) {
            $this->registerBulkActionMacros();
        }

        if (class_exists(Entry::class)) {
            $this->registerInfolistEntryMacros();
        }

        if (class_exists(Widget::class)) {
            $this->registerWidgetMacros();
        }
    }

    protected function registerSchemaComponentMacros(): void
    {
        SchemaComponent::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        SchemaComponent::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        SchemaComponent::macro('aiCanFill', function (bool $canFill = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Fill, $canFill);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Fill, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        SchemaComponent::macro('aiCanSave', function (bool $canSave = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Save, $canSave);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Save, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        SchemaComponent::macro('aiCanDraft', function (bool $canDraft = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Draft, $canDraft);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Draft, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        SchemaComponent::macro('aiCanRead', function (bool $canRead = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Read, $canRead);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Read, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        SchemaComponent::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        SchemaComponent::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerColumnMacros(): void
    {
        Column::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        Column::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        Column::macro('aiCanRead', function (bool $canRead = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Read, $canRead);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Read, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Column::macro('aiCanSearch', function (bool $canSearch = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Search, $canSearch);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Search, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Column::macro('aiCanSort', function (bool $canSort = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Sort, $canSort);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Sort, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Column::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        Column::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerFilterMacros(): void
    {
        BaseFilter::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        BaseFilter::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        BaseFilter::macro('aiCanUse', function (bool $canUse = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Filter, $canUse);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Filter, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        BaseFilter::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        BaseFilter::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerActionMacros(): void
    {
        Action::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        Action::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        Action::macro('aiCanExecute', function (bool $canExecute = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Execute, $canExecute);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Execute, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Action::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        Action::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerBulkActionMacros(): void
    {
        BulkAction::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        BulkAction::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        BulkAction::macro('aiCanExecute', function (bool $canExecute = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Execute, $canExecute);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Execute, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        BulkAction::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        BulkAction::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerInfolistEntryMacros(): void
    {
        Entry::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        Entry::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        Entry::macro('aiCanRead', function (bool $canRead = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Read, $canRead);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Read, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Entry::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        Entry::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    protected function registerWidgetMacros(): void
    {
        Widget::macro('aiDescription', function (string $description) {
            MacroRegistrar::setDescription($this, $description);

            return $this;
        });

        Widget::macro('getAiDescription', function (): ?string {
            return MacroRegistrar::getDescription($this);
        });

        Widget::macro('aiCanInteract', function (bool $canInteract = true, ?string $description = null, bool $needToAsk = false) {
            MacroRegistrar::setCapability($this, CapabilityType::Interact, $canInteract);
            MacroRegistrar::setNeedToAsk($this, CapabilityType::Interact, $needToAsk);

            if ($description !== null) {
                MacroRegistrar::setDescription($this, $description);
            }

            return $this;
        });

        Widget::macro('getAiCapabilities', function (): array {
            return MacroRegistrar::getCapabilities($this);
        });

        Widget::macro('getAiNeedToAsk', function (?CapabilityType $type = null): bool {
            return MacroRegistrar::getNeedToAsk($this, $type);
        });
    }

    public static function setDescription(object $component, string $description): void
    {
        static::$componentDescriptions[spl_object_id($component)] = $description;
    }

    public static function getDescription(object $component): ?string
    {
        return static::$componentDescriptions[spl_object_id($component)] ?? null;
    }

    public static function setCapability(object $component, CapabilityType $type, bool $enabled): void
    {
        $id = spl_object_id($component);

        if (! isset(static::$componentCapabilities[$id])) {
            static::$componentCapabilities[$id] = [];
        }

        static::$componentCapabilities[$id][$type->value] = $enabled;
    }

    public static function getCapabilities(object $component): array
    {
        return static::$componentCapabilities[spl_object_id($component)] ?? [];
    }

    public static function hasCapability(object $component, CapabilityType $type): bool
    {
        $capabilities = static::getCapabilities($component);

        return $capabilities[$type->value] ?? false;
    }

    /**
     * Set whether a capability requires user confirmation before the agent can act.
     */
    public static function setNeedToAsk(object $component, CapabilityType $type, bool $needToAsk): void
    {
        $id = spl_object_id($component);

        if (! isset(static::$componentNeedToAsk[$id])) {
            static::$componentNeedToAsk[$id] = [];
        }

        static::$componentNeedToAsk[$id][$type->value] = $needToAsk;

        // Track the field name globally when needToAsk is true
        if ($needToAsk && method_exists($component, 'getName')) {
            $name = $component->getName();
            if ($name && ! in_array($name, static::$needToAskFieldNames, true)) {
                static::$needToAskFieldNames[] = $name;
            }
        }
    }

    /**
     * Check if a capability requires user confirmation.
     * If $type is null, returns true if ANY capability requires confirmation.
     */
    public static function getNeedToAsk(object $component, ?CapabilityType $type = null): bool
    {
        $id = spl_object_id($component);
        $askFlags = static::$componentNeedToAsk[$id] ?? [];

        if ($type !== null) {
            return $askFlags[$type->value] ?? false;
        }

        // If no specific type, return true if any has needToAsk
        foreach ($askFlags as $flag) {
            if ($flag) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all needToAsk flags for a component.
     */
    public static function getNeedToAskFlags(object $component): array
    {
        return static::$componentNeedToAsk[spl_object_id($component)] ?? [];
    }

    /**
     * Get all globally tracked field names that have needToAsk=true.
     */
    public static function getTrackedNeedToAskFieldNames(): array
    {
        return static::$needToAskFieldNames;
    }
}
