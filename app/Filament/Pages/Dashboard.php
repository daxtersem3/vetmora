<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveStaffWidget;
use App\Filament\Widgets\CitasChartWidget;
use App\Filament\Widgets\CitasMensualesChartWidget;
use App\Filament\Widgets\CitasPendientesWidget;
use App\Filament\Widgets\CitasStatsWidget;
use App\Filament\Widgets\MembresiasChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Escritorio';

    public function getWidgets(): array
    {
        return [
            CitasStatsWidget::class,        // Métricas principales (6 cards)
            ActiveStaffWidget::class,        // Tabla personal activo
            CitasMensualesChartWidget::class, // Gráfico línea: entradas/salidas por mes
            MembresiasChartWidget::class,    // Gráfico barras: membresías
            CitasPendientesWidget::class,    // Tabla citas pendientes
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
