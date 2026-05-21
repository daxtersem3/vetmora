<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MembresiasChartWidget extends ChartWidget
{
    protected ?string $heading = ' Membresías por Estado';

    protected ?string $description = 'Distribución de membresías vigentes y vencidas';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $vigentes = DB::table('membresias')->where('estado', 'vigente')->count();
        $vencidas = DB::table('membresias')->where('estado', 'vencida')->count();

        return [
            'labels' => ['Vigentes', 'Vencidas'],
            'datasets' => [
                [
                    'label' => 'Membresías',
                    'data' => [$vigentes, $vencidas],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 8,
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
                    'display' => false,
                ],
            ],
        ];
    }
}
