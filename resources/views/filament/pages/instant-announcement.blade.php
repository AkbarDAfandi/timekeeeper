<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Broadcast Now</span>
                <span wire:loading>Broadcasting...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
