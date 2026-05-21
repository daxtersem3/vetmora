<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CitasMensualesChartWidget extends ChartWidget
{
    protected ?string $heading = '📅 Entrada y Salida de Citas por Mes';

    protected ?string $description = 'Citas pendientes vs tomadas en los últimos 6 meses';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $meses = collect();
        $pendientesData = collect();
        $tomadasData = collect();

        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $inicio = $fecha->copy()->startOfMonth();
            $fin = $fecha->copy()->endOfMonth();

            $meses->push($fecha->translatedFormat('M Y'));

            $pendientesData->push(
                DB::table('citas')
                    ->whereBetween('fecha_hora', [$inicio, $fin])
                    ->where('estado', 'pendiente')
                    ->count()
            );

            $tomadasData->push(
                DB::table('citas')
                    ->whereBetween('fecha_hora', [$inicio, $fin])
                    ->where('estado', 'tomada')
                    ->count()
            );
        }

        return [
            'labels' => $meses->toArray(),
            'datasets' => [
                [
                    'label' => 'Pendientes (Entran)',
                    'data' => $pendientesData->toArray(),
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgb(251, 191, 36)',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
                [
                    'label' => 'Tomadas (Salen)',
                    'data' => $tomadasData->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}
