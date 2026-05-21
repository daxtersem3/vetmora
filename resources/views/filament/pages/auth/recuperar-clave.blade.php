<x-filament-panels::page.simple>
    {{-- Step indicator --}}
    <div style="display:flex;gap:6px;margin-bottom:16px;justify-content:center;align-items:center;">
        @foreach([1 => 'Correo', 2 => 'Preguntas', 3 => 'Nueva Clave'] as $i => $lbl)
            <div style="display:flex;align-items:center;gap:4px;">
                <div
                    style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;background:{{ $step >= $i ? '#7c3aed' : '#e5e7eb' }};color:{{ $step >= $i ? '#fff' : '#6b7280' }};">
                    {{ $i }}</div>
                <span style="font-size:11px;color:{{ $step >= $i ? '#7c3aed' : '#9ca3af' }};">{{ $lbl }}</span>
                @if($i < 3)<span style="color:#d1d5db;margin:0 2px;">›</span>@endif
            </div>
        @endforeach
    </div>

    {{-- Error --}}
    @if($error)
        <div
            style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:13px;">
            {{ $error }}
        </div>
    @endif

    <form wire:submit.prevent="
        @if($step === 1) buscarUsuario
        @elseif($step === 2) validarRespuestas
        @else actualizarClave
        @endif
    " style="display:flex;flex-direction:column;gap:12px;">

        @if($step === 1)
            <div>
                <label style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;color:#374151;">Correo
                    electrónico</label>
                <input type="email" wire:model="email" required
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;"
                    placeholder="tucorreo@ejemplo.com" />
            </div>
        @endif

        @if($step === 2)
            <div>
                <label
                    style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;color:#374151;">{{ $pregunta_1 }}</label>
                <input type="password" wire:model="respuesta_1" required
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;" />
            </div>
            <div>
                <label
                    style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;color:#374151;">{{ $pregunta_2 }}</label>
                <input type="password" wire:model="respuesta_2" required
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;" />
            </div>
            <p style="font-size:11px;color:#9ca3af;">Las respuestas no distinguen mayúsculas.</p>
        @endif

        @if($step === 3)
            <div>
                <label style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;color:#374151;">Nueva
                    contraseña</label>
                <input type="password" wire:model="nueva_password" required minlength="8"
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;" />
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;color:#374151;">Confirmar
                    contraseña</label>
                <input type="password" wire:model="nueva_password_confirmation" required
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;" />
            </div>
            <p style="font-size:11px;color:#9ca3af;">Mínimo 8 caracteres.</p>
        @endif

        <div style="display:flex;gap:8px;">
            @if($step > 1)
                <button type="button" wire:click="volver"
                    style="flex:1;padding:9px;border:1px solid #d1d5db;border-radius:8px;background:#f9fafb;cursor:pointer;font-size:13px;">
                    ← Volver
                </button>
            @endif
            <button type="submit"
                style="flex:1;padding:9px;border:none;border-radius:8px;background:#7c3aed;color:#fff;cursor:pointer;font-size:13px;font-weight:600;">
                @if($step === 1) Continuar
                @elseif($step === 2) Verificar respuestas
                @else Cambiar contraseña
                @endif
            </button>
        </div>
    </form>

    <div style="text-align:center;margin-top:12px;">
        <a href="{{ filament()->getLoginUrl() }}" style="font-size:12px;color:#7c3aed;">← Volver al login</a>
    </div>
</x-filament-panels::page.simple>