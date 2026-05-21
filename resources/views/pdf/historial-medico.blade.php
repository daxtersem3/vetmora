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
            padding: 20px 30px 90px 30px;
            /* bottom padding para no tapar el footer fijo */
        }

        /* ---- HEADER ---- */
        .header {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 3px solid #00B8C6;
            padding-bottom: 10px;
        }

        .header-logo {
            float: left;
            width: 50%;
        }

        .header-logo img {
            width: 160px;
        }

        .header-mascota {
            float: right;
            width: 40%;
            text-align: right;
        }

        .header-mascota img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid #7B2D8B;
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
            font-size: 11px;
            padding: 5px 10px;
            margin-top: 12px;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
        }

        /* ---- TABLES ---- */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        table tr:nth-child(even) td {
            background: #f0fafb;
        }

        table tr:nth-child(odd) td {
            background: #ffffff;
        }

        .label-col {
            width: 35%;
            font-weight: bold;
            color: #444;
        }

        /* ---- GRID (checkboxes) ---- */
        .grid-table td {
            width: 25%;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
            padding: 4px 6px;
        }

        /* ---- FOOTER fijo al fondo de la página ---- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 30px;
            right: 30px;
            border-top: 2px solid #7B2D8B;
            padding-top: 6px;
            text-align: center;
            background: #fff;
        }

        .footer .address {
            font-size: 9px;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer .disclaimer {
            font-size: 8.5px;
            color: #777;
            margin-top: 4px;
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

    <!-- DATOS DEL PROPIETARIO -->
    <div class="section-title">Datos del Propietario</div>
    @php $cliente = $historial->cita->cliente ?? null; @endphp
    <table>
        <tr>
            <td class="label-col">Nombre</td>
            <td>{{ $cliente->nombre ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Cédula</td>
            <td>{{ $cliente->cedula ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Correo</td>
            <td>{{ $cliente->correo ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Teléfono</td>
            <td>{{ $cliente->telefono ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Dirección</td>
            <td>{{ $cliente->direccion ?? '-' }}</td>
        </tr>
    </table>

    <!-- DATOS DE LA MASCOTA -->
    <div class="section-title">Datos de la Mascota</div>
    <table>
        <tr>
            <td class="label-col">Nombre</td>
            <td>{{ $mascota->nombre ?? '-' }}</td>
            <td class="label-col">Especie</td>
            <td>{{ $mascota->especie ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Raza</td>
            <td>{{ $mascota->raza ?? '-' }}</td>
            <td class="label-col">Fecha de Nacimiento</td>
            <td>{{ $mascota->fecha_nacimiento ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('Y-m-d') : '-' }}
            </td>
        </tr>
    </table>

    <!-- HISTORIAL MÉDICO -->
    <div class="section-title">Historial Médico</div>
    @php $vet = $historial->cita->veterinario ?? null; @endphp
    <table>
        <tr>
            <td class="label-col">Veterinario</td>
            <td>{{ $vet->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Fecha</td>
            <td>{{ $historial->fecha ? \Carbon\Carbon::parse($historial->fecha)->format('Y-m-d') : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Síntomas</td>
            <td>{{ $historial->sintomas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Diagnóstico</td>
            <td>{{ $historial->diagnostico ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tratamiento</td>
            <td>{{ $historial->tratamiento ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Observaciones</td>
            <td>{{ $historial->observaciones ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Peso</td>
            <td>{{ $historial->peso ? number_format($historial->peso, 2) . ' kg' : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Temperatura</td>
            <td>{{ $historial->temperatura ? number_format($historial->temperatura, 2) . ' °C' : '-' }}</td>
        </tr>
    </table>

    <!-- SISTEMAS EVALUADOS -->
    @if(!empty($historial->sistemas_evaluados))
        <div class="section-title">Sistemas Evaluados</div>
        <table class="grid-table">
            @foreach(array_chunk($historial->sistemas_evaluados, 4) as $row)
                <tr>
                    @foreach($row as $sistema)<td>{{ $sistema }}</td>@endforeach
                    @for($i = count($row); $i < 4; $i++)
                    <td></td>@endfor
                </tr>
            @endforeach
        </table>
    @endif

    <!-- EXÁMENES REALIZADOS -->
    @if(!empty($historial->examenes_realizados))
        <div class="section-title">Exámenes Realizados</div>
        <table class="grid-table">
            @foreach(array_chunk($historial->examenes_realizados, 4) as $row)
                <tr>
                    @foreach($row as $examen)<td>{{ $examen }}</td>@endforeach
                    @for($i = count($row); $i < 4; $i++)
                    <td></td>@endfor
                </tr>
            @endforeach
        </table>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <div class="address">
            Av. 17 de Diciembre cruce con Av. San Vicente de Paul, Parroquia Catedral,<br>
            Ciudad Bolívar, Estado Bolívar, Código Postal 8001
        </div>
        <div class="disclaimer">
            La historia clínica se basa en la evaluación física, exámenes complementarios (según el caso), y en la
            información
            suministrada por el Tutor, el cual se compromete a cumplir con las indicaciones y recomendaciones para el
            cuidado y bienestar de su mascota.
        </div>
    </div>

</body>

</html>