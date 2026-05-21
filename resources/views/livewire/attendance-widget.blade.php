<div class="flex justify-center mt-4">
    <x-filament::modal alignment="center" width="xs">
        <x-slot name="trigger">
            <x-filament::button size="lg">
                Asistencia
            </x-filament::button>
        </x-slot>

        <x-slot name="heading">
            Registro de Asistencia
        </x-slot>

        <div style="display:flex;flex-direction:column;gap:20px;padding:8px 0;">

            {{-- Cédula field --}}
            <div>
                {{ $this->form }}
            </div>

            {{-- Divider --}}
            <div style="border-top:1px solid #e5e7eb;"></div>

            {{-- Action buttons --}}
            <div style="display:flex;flex-direction:column;gap:10px;">
                <x-filament::button wire:click="checkIn" color="success" size="lg" style="width:100%;">
                    Registrar Entrada
                </x-filament::button>
                <x-filament::button wire:click="checkOut" color="danger" size="lg" style="width:100%;">
                    Registrar Salida
                </x-filament::button>
            </div>

        </div>
    </x-filament::modal>
</div>