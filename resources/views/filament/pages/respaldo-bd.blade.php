<x-filament-panels::page>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 50vh; padding: 2rem 0;">
        <!-- Usamos estilos en línea (CSS puro) para garantizar que se apliquen, ya que las clases de Tailwind nuevas no se compilan hasta ejecutar npm run build -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; width: 100%; max-width: 800px;">
            
            {{-- Export Section --}}
            <div style="display: flex; flex-direction: column;">
                <x-filament::section icon="heroicon-o-arrow-down-tray">
                    <x-slot name="heading">
                        Descargar Respaldo
                    </x-slot>
                    
                    <x-slot name="description">
                        Genera una copia de seguridad en formato .sql para guardarla localmente.
                    </x-slot>

                    <div style="margin-top: 2rem; display: flex; width: 100%;">
                        <x-filament::button 
                            href="{{ route('db.backup.download') }}" 
                            tag="a" 
                            color="primary" 
                            icon="heroicon-m-arrow-down-tray"
                            style="width: 100%; justify-content: center;"
                        >
                            Descargar archivo .sql
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>

            {{-- Import Section --}}
            <div style="display: flex; flex-direction: column;">
                <x-filament::section icon="heroicon-o-arrow-up-tray">
                    <x-slot name="heading">
                        Importar Respaldo
                    </x-slot>
                    
                    <x-slot name="description">
                        Sube un archivo de respaldo .sql para restaurar y reemplazar el sistema.
                    </x-slot>

                    <div style="margin-top: 2rem; display: flex; width: 100%;">
                        <x-filament::button 
                            wire:click="mountAction('importar')" 
                            color="danger" 
                            icon="heroicon-m-arrow-up-tray"
                            style="width: 100%; justify-content: center;"
                        >
                            Importar archivo .sql
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>

        </div>
    </div>

    {{-- Acciones invisibles para el registro de Filament --}}
    <div style="display: none;">
        {{ $this->importar }}
        {{ $this->descargar }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
