<x-filament-panels::page>
    <form wire:submit="submit" class="fi-form space-y-6">
        {{ $this->form }}

        <x-filament::actions 
            :actions="$this->getFormActions()" 
        />
    </form>
</x-filament-panels::page>