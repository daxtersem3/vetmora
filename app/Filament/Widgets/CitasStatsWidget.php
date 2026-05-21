<?php

namespace App\Filament\Widgets;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Membresia;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CitasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // Citas del mes actual
        $citasMes = Cita::whereBetween('fecha_hora', [$inicioMes, $finMes])->count();
        $pendientesMes = Cita::whereBetween('fecha_hora', [$inicioMes, $finMes])
            ->where('estado', 'pendiente')->count();
        $tomadasMes = Cita::whereBetween('fecha_hora', [$inicioMes, $finMes])
            ->where('estado', 'tomada')->count();

        // Comparar con mes anterior
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        $citasMesAnterior = Cita::whereBetween('fecha_hora', [$inicioMesAnterior, $finMesAnterior])->count();

        $diferencia = $citasMes - $citasMesAnterior;
        $descripcionTrend = $diferencia >= 0
            ? "+{$diferencia} vs mes anterior"
            : "{$diferencia} vs mes anterior";

        // Totales generales
        $totalClientes = Cliente::count();
        $totalMascotas = Mascota::count();
        $membresiasVigentes = Membresia::where('estado', 'vigente')->count();

        // Mini chart data: citas por día del mes actual (últimos 7 días)
        $chartPendientes = [];
        $chartTomadas = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::now()->subDays($i);
            $chartPendientes[] = Cita::whereDate('fecha_hora', $dia)->where('estado', 'pendiente')->count();
            $chartTomadas[] = Cita::whereDate('fecha_hora', $dia)->where('estado', 'tomada')->count();
        }

        return [
            Stat::make('Citas del Mes', $citasMes)
                ->description($descripcionTrend)
                ->descriptionIcon($diferencia >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($diferencia >= 0 ? 'success' : 'danger')
                ->chart(array_map(fn($p, $t) => $p + $t, $chartPendientes, $chartTomadas)),

            Stat::make('Pendientes (Entran)', $pendientesMes)
                ->description('Citas por atender este mes')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($chartPendientes),

            Stat::make('Tomadas (Salen)', $tomadasMes)
                ->description('Citas atendidas este mes')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($chartTomadas),

            Stat::make('Clientes Registrados', $totalClientes)
                ->description('Total en el sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Mascotas Registradas', $totalMascotas)
                ->description('Total en el sistema')
                ->descriptionIcon('heroicon-m-heart')
                ->color('primary'),

            Stat::make('Membresías Vigentes', $membresiasVigentes)
                ->description('Activas actualmente')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }
}
