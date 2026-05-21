<?php

namespace App\Http\Controllers;

use App\Models\HistorialMedico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HistorialMedicoPdfController extends Controller
{
    public function __invoke(HistorialMedico $historial)
    {
        $historial->load([
            'cita.cliente',
            'cita.mascota',
            'cita.veterinario',
        ]);

        $pdf = Pdf::loadView('pdf.historial-medico', compact('historial'))
            ->setPaper('a4', 'portrait');

        $mascotaNombre = $historial->cita->mascota->nombre ?? 'historial';
        $fecha = $historial->fecha
            ? \Carbon\Carbon::parse($historial->fecha)->format('Y-m-d')
            : now()->format('Y-m-d');

        return $pdf->download("historial-{$mascotaNombre}-{$fecha}.pdf");
    }
}
