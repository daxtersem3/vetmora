<?php

namespace App\Http\Controllers;

use App\Models\Cirugia;
use Barryvdh\DomPDF\Facade\Pdf;

class CirugiaPdfController extends Controller
{
    public function __invoke(Cirugia $cirugia)
    {
        $cirugia->load(['cliente', 'mascota', 'veterinario']);

        $pdf = Pdf::loadView('pdf.cirugia', compact('cirugia'))
            ->setPaper('a4', 'portrait');

        $mascotaNombre = $cirugia->mascota->nombre ?? 'cirugia';
        $fecha = $cirugia->fecha
            ? \Carbon\Carbon::parse($cirugia->fecha)->format('Y-m-d')
            : now()->format('Y-m-d');

        return $pdf->download("cirugia-{$mascotaNombre}-{$fecha}.pdf");
    }
}
