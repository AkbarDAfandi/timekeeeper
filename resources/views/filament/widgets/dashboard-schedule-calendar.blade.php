@php
    $plugin = \Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::get();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex justify-end flex-1 mb-4">
            <x-filament::actions :actions="$this->getCachedHeaderActions()" class="shrink-0" />
        </div>

        <div class="relative">
            <div wire:loading.class="opacity-50 pointer-events-none" class="transition-all duration-200">
                <div wire:ignore x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-fullcalendar-alpine', 'saade/filament-fullcalendar') }}"
                    x-ignore x-data="fullcalendar({
                        locale: @js($plugin->getLocale()),
                        plugins: @js($plugin->getPlugins()),
                        schedulerLicenseKey: @js($plugin->getSchedulerLicenseKey()),
                        timeZone: @js($plugin->getTimezone()),
                        config: @js($this->getConfig()),
                        editable: @json($plugin->isEditable()),
                        selectable: @json($plugin->isSelectable()),
                        eventClassNames: {!! htmlspecialchars($this->eventClassNames(), ENT_COMPAT) !!},
                        eventContent: {!! htmlspecialchars($this->eventContent(), ENT_COMPAT) !!},
                        eventDidMount: {!! htmlspecialchars($this->eventDidMount(), ENT_COMPAT) !!},
                        eventWillUnmount: {!! htmlspecialchars($this->eventWillUnmount(), ENT_COMPAT) !!},
                    })" class="filament-fullcalendar"></div>
            </div>

            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-900/70 rounded-xl z-10">
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
