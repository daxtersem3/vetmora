<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Membership Info Card --}}
        <div
            class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Left: Membership Details --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-primary-500" />
                        Datos de la Membresía
                    </h3>

                    <div class="space-y-2">
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</span>
                            <span
                                class="text-sm font-bold text-primary-600 dark:text-primary-400 font-mono tracking-wider">{{ $record->codigo_activacion }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Activación</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->fecha_activacion->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Vencimiento</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->fecha_vencimiento->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</span>
                            @if($record->estado === 'vigente')
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span>
                                    Vigente
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-danger-500"></span>
                                    Vencida
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right: Client & Pet --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-user class="w-5 h-5 text-primary-500" />
                        Cliente y Mascota
                    </h3>

                    <div class="space-y-2">
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->cliente->nombre ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Cédula</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->cliente->cedula ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Mascota</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->mascota->nombre ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Especie</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->mascota->especie ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Raza</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $record->mascota->raza ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Services Section --}}
        <div
            class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-primary-500" />
                Servicios Incluidos
            </h3>

            @php
                $completados = $record->servicios->where('realizado', true)->count();
                $total = $record->servicios->count();
                $progreso = $total > 0 ? round(($completados / $total) * 100) : 0;
            @endphp

            {{-- Progress bar --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-1">
                    <span>Progreso</span>
                    <span>{{ $completados }}/{{ $total }} completados</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $progreso === 100 ? 'bg-success-500' : 'bg-primary-500' }}"
                        style="width: {{ $progreso }}%"></div>
                </div>
            </div>

            {{-- Service cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($record->servicios as $servicio)
                            <div class="relative rounded-lg border-2 p-4 transition-all duration-200 cursor-pointer
                                    {{ $servicio->realizado
                    ? 'border-success-300 bg-success-50 dark:border-success-600 dark:bg-success-900/20'
                    : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 hover:border-primary-300 dark:hover:border-primary-600'
                                    }}" wire:click="marcarServicio({{ $servicio->id }})" @if($record->estado === 'vencida')
                                    title="Membresía vencida" @endif>
                                {{-- Icon --}}
                                <div class="flex items-center gap-3 mb-2">
                                    @if($servicio->tipo === 'consulta')
                                        <x-heroicon-o-heart
                                            class="w-8 h-8 {{ $servicio->realizado ? 'text-success-500' : 'text-gray-400' }}" />
                                    @elseif($servicio->tipo === 'desparasitacion_corte')
                                        <x-heroicon-o-scissors
                                            class="w-8 h-8 {{ $servicio->realizado ? 'text-success-500' : 'text-gray-400' }}" />
                                    @else
                                        <x-heroicon-o-computer-desktop
                                            class="w-8 h-8 {{ $servicio->realizado ? 'text-success-500' : 'text-gray-400' }}" />
                                    @endif

                                    <div>
                                        <p
                                            class="font-semibold text-sm {{ $servicio->realizado ? 'text-success-700 dark:text-success-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $servicio->nombre }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Status --}}
                                @if($servicio->realizado)
                                    <div class="flex items-center gap-1 text-xs text-success-600 dark:text-success-400">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                        Realizado el {{ $servicio->fecha_realizado?->format('d/m/Y') }}
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-clock class="w-4 h-4" />
                                        Pendiente — Click para marcar
                                    </div>
                                @endif

                                {{-- Checkmark overlay --}}
                                @if($servicio->realizado)
                                    <div class="absolute top-2 right-2">
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-success-500" />
                                    </div>
                                @endif
                            </div>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>