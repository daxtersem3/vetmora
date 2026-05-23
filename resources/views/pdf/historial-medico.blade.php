<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial Médico - {{ $historial->cita->mascota->nombre ?? 'Paciente' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.25;
            padding: 10px 20px 75px 20px;
            /* bottom padding to avoid overlapping the fixed footer */
        }

        /* ---- HEADER ---- */
        .header {
            width: 100%;
            margin-bottom: 6px;
            border-bottom: 2px solid #00B8C6;
            padding-bottom: 4px;
            height: 75px;
        }

        .header-logo {
            float: left;
            width: 50%;
        }

        .header-logo img {
            height: 60px;
            width: auto;
        }

        .header-mascota {
            float: right;
            width: 40%;
            text-align: right;
        }

        .header-mascota img {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #7B2D8B;
        }

        .clearfix::after {
            content: '';
            display: table;
            clear: both;
        }

        /* ---- SECTION TITLE ---- */
        .section-title {
            background: #00B8C6;
            color: #fff;
            font-weight: bold;
            font-size: 10.5px;
            padding: 3px 6px;
            margin-top: 6px;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
        }

        /* ---- TABLES ---- */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td {
            padding: 4px 7px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        table tr:nth-child(even) td {
            background: #f0fafb;
        }

        table tr:nth-child(odd) td {
            background: #ffffff;
        }

        .label-col {
            font-weight: bold;
            color: #444;
            background: #f9f9f9 !important;
        }

        /* ---- GRID (checkboxes) ---- */
        .grid-table td {
            width: 33.33%;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
            padding: 2px 4px;
            font-size: 9.5px;
        }

        /* ---- FOOTER fijo al fondo de la página ---- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 20px;
            right: 20px;
            border-top: 2px solid #7B2D8B;
            padding-top: 4px;
            text-align: center;
            background: #fff;
        }

        .footer .address {
            font-size: 8px;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer .disclaimer {
            font-size: 7.5px;
            color: #777;
            margin-top: 2px;
            font-style: italic;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header clearfix">
        <div class="header-logo">
            @if(file_exists(public_path('images/logo-vetmora.png')))
                <img src="{{ public_path('images/logo-vetmora.png') }}" alt="VetMora">
            @endif
        </div>
        <div class="header-mascota">
            @php
                $mascota = $historial->cita->mascota ?? null;
                $fotoPath = $mascota && $mascota->foto
                    ? storage_path('app/public/' . $mascota->foto)
                    : null;
            @endphp
            @if($fotoPath && file_exists($fotoPath))
                <img src="{{ $fotoPath }}" alt="Foto mascota">
            @endif
        </div>
    </div>

    <!-- CLIENTE Y MASCOTA EN PARALELO -->
    <table style="width: 100%; border: none; margin-bottom: 5px;">
        <tr>
            <td style="width: 49%; border: none; padding: 0; vertical-align: top;">
                <div class="section-title" style="margin-top: 0;">Datos del Propietario</div>
                @php $cliente = $historial->cita->cliente ?? null; @endphp
                <table style="width: 100%;">
                    <tr>
                        <td class="label-col" style="width: 30%;">Nombre</td>
                        <td>{{ $cliente->nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Cédula</td>
                        <td>{{ $cliente->cedula ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Teléfono</td>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Correo</td>
                        <td>{{ $cliente->correo ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Dirección</td>
                        <td>{{ $cliente->direccion ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%; border: none; padding: 0;"></td>
            <td style="width: 49%; border: none; padding: 0; vertical-align: top;">
                <div class="section-title" style="margin-top: 0;">Datos de la Mascota</div>
                <table style="width: 100%;">
                    <tr>
                        <td class="label-col" style="width: 30%;">Nombre</td>
                        <td>{{ $mascota->nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Especie</td>
                        <td>{{ $mascota->especie ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Raza</td>
                        <td>{{ $mascota->raza ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Nacimiento</td>
                        <td>{{ $mascota->fecha_nacimiento ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Edad</td>
                        <td>{{ $mascota->fecha_nacimiento ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->age . ' años' : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ANAMNESIS -->
    @php
        $tieneAnamnesis = (
            !empty($historial->anamnesis_motivo_consulta) ||
            !empty($historial->anamnesis_dieta) ||
            !empty($historial->anamnesis_vomito) ||
            !empty($historial->anamnesis_diarrea) ||
            !empty($historial->anamnesis_garrapatas) ||
            !empty($historial->anamnesis_esquema_vacunal) ||
            !empty($historial->anamnesis_desparasitacion) ||
            !empty($historial->anamnesis_enfermedades_previas) ||
            !empty($historial->anamnesis_tx_recientes) ||
            !empty($historial->anamnesis_esterilizado) ||
            $historial->anamnesis_num_partos !== null ||
            !empty($historial->anamnesis_vive_con_animales) ||
            !empty($historial->anamnesis_cuales_animales)
        );
    @endphp
    @if($tieneAnamnesis)
    <div class="section-title">Anamnesis</div>
    <table>
        <tr>
            <td class="label-col" style="width: 20%;">Motivo Consulta</td>
            <td colspan="3">{{ $historial->anamnesis_motivo_consulta ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col" style="width: 20%;">Dieta</td>
            <td style="width: 30%;">{{ $historial->anamnesis_dieta ?? '-' }}</td>
            <td class="label-col" style="width: 20%;">Esquema Vacunal</td>
            <td style="width: 30%;">{{ $historial->anamnesis_esquema_vacunal ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Desparasitación</td>
            <td>{{ $historial->anamnesis_desparasitacion ?? '-' }}</td>
            <td class="label-col">Enfermedades Previas</td>
            <td>{{ $historial->anamnesis_enfermedades_previas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tx Recientes</td>
            <td>{{ $historial->anamnesis_tx_recientes ?? '-' }}</td>
            <td class="label-col">Esterilizado / Partos</td>
            <td>
                {{ $historial->anamnesis_esterilizado ?? '-' }}
                @if($historial->anamnesis_num_partos !== null && $historial->anamnesis_num_partos > 0)
                    / {{ $historial->anamnesis_num_partos }} partos
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-col">¿Vive con animales?</td>
            <td>
                {{ $historial->anamnesis_vive_con_animales ?? '-' }}
                @if($historial->anamnesis_cuales_animales)
                    ({{ $historial->anamnesis_cuales_animales }})
                @endif
            </td>
            <td class="label-col">Estado Sintomático</td>
            <td>
                Vómito: <strong>{{ $historial->anamnesis_vomito ?? 'No' }}</strong> |
                Diarrea: <strong>{{ $historial->anamnesis_diarrea ?? 'No' }}</strong> |
                Garrapatas: <strong>{{ $historial->anamnesis_garrapatas ?? 'No' }}</strong>
            </td>
        </tr>
    </table>
    @endif

    <!-- HISTORIAL MÉDICO -->
    <div class="section-title">Historial Médico</div>
    @php $vet = $historial->cita->veterinario ?? null; @endphp
    <table>
        <tr>
            <td class="label-col" style="width: 20%;">Veterinario</td>
            <td style="width: 30%;">{{ $vet->name ?? '-' }}</td>
            <td class="label-col" style="width: 20%;">Fecha</td>
            <td style="width: 30%;">{{ $historial->fecha ? \Carbon\Carbon::parse($historial->fecha)->format('Y-m-d') : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Síntomas</td>
            <td colspan="3">{{ $historial->sintomas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Diagnóstico</td>
            <td colspan="3">{{ $historial->diagnostico ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tratamiento</td>
            <td colspan="3">{{ $historial->tratamiento ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Observaciones</td>
            <td colspan="3">{{ $historial->observaciones ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Peso</td>
            <td>{{ $historial->peso ? number_format($historial->peso, 2) . ' kg' : '-' }}</td>
            <td class="label-col">Temperatura</td>
            <td>{{ $historial->temperatura ? number_format($historial->temperatura, 2) . ' °C' : '-' }}</td>
        </tr>
    </table>

    <!-- SISTEMAS EVALUADOS Y EXÁMENES REALIZADOS EN PARALELO -->
    @if(!empty($historial->sistemas_evaluados) || !empty($historial->examenes_realizados))
    <table style="width: 100%; border: none; margin-top: 5px;">
        <tr>
            <td style="width: 49%; border: none; padding: 0; vertical-align: top;">
                @if(!empty($historial->sistemas_evaluados))
                    <div class="section-title" style="margin-top: 0;">Sistemas Evaluados</div>
                    <table class="grid-table">
                        @foreach(array_chunk($historial->sistemas_evaluados, 3) as $row)
                            <tr>
                                @foreach($row as $sistema)<td style="width: 33%;">{{ $sistema }}</td>@endforeach
                                @for($i = count($row); $i < 3; $i++)
                                <td style="width: 33%;"></td>@endfor
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
            <td style="width: 2%; border: none; padding: 0;"></td>
            <td style="width: 49%; border: none; padding: 0; vertical-align: top;">
                @if(!empty($historial->examenes_realizados))
                    <div class="section-title" style="margin-top: 0;">Exámenes Realizados</div>
                    <table class="grid-table">
                        @foreach(array_chunk($historial->examenes_realizados, 3) as $row)
                            <tr>
                                @foreach($row as $examen)<td style="width: 33%;">{{ $examen }}</td>@endforeach
                                @for($i = count($row); $i < 3; $i++)
                                <td style="width: 33%;"></td>@endfor
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <div class="address">
            Av. 17 de Diciembre cruce con Av. San Vicente de Paul, Parroquia Catedral,<br>
            Ciudad Bolívar, Estado Bolívar, Código Postal 8001
        </div>
        <div class="disclaimer">
            La historia clínica se basa en la evaluación física, exámenes complementarios (según el caso), y en la información suministrada por el Tutor, el cual se compromete a cumplir con las indicaciones y recomendaciones para el cuidado y bienestar de su mascota.
        </div>
    </div>

</body>

</html>