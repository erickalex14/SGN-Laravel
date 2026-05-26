@php
    $orden = $informe->orden;
    $ordenEmpresa = $informe->ordenEmpresa ?? null;
    $cliente = $orden?->cliente;
    $empresa = $ordenEmpresa?->empresa;
    $equipo = $orden?->equipo ?? $ordenEmpresa?->equipo;
    $tecnico = $informe->tecnico;
    $repuestosUsados = $orden?->ordenRepuestos ?? collect();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe {{ $orden?->nro_orden ?? $ordenEmpresa?->nro_orden ?? $informe->id }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #fff; color: #0f172a; font-size: 12px; }
.page { width: 100%; max-width: 190mm; margin: 0 auto; padding: 8mm; }
.print-btn { position: fixed; top: 12px; right: 12px; border: 0; background: #1d4ed8; color: #fff; padding: 10px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }
.header { border-bottom: 1px solid #0f172a; padding-bottom: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; }
.title { font-size: 20px; font-weight: 800; }
.meta { text-align: right; line-height: 1.5; }
.sec { margin-bottom: 10px; }
.sec h3 { font-size: 12px; text-transform: uppercase; background: #dbeafe; border-left: 3px solid #1d4ed8; padding: 4px 8px; margin-bottom: 4px; }
table { width: 100%; border-collapse: collapse; }
td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: top; }
.lbl { display: block; font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; font-weight: 700; }
.txt { border: 1px solid #cbd5e1; padding: 8px; line-height: 1.45; min-height: 70px; white-space: pre-wrap; }
.fotos { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.foto-card { border: 1px solid #cbd5e1; padding: 6px; }
.foto-card img { width: 100%; height: 190px; object-fit: cover; border: 1px solid #e2e8f0; }
.foto-cap { margin-top: 4px; font-size: 10px; color: #475569; }
@media print {
    @page { size: A4 portrait; margin: 10mm; }
    .print-btn { display: none; }
    .page { padding: 0; }
}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
<div class="page">
    <div class="header">
        <div>
            <div class="title">Informe Tecnico</div>
            <div>Novitecnologia Cia. Ltda.</div>
        </div>
        <div class="meta">
            <div><strong>Orden {{ $orden?->nro_orden ?? $ordenEmpresa?->nro_orden ?? '-' }}</strong></div>
            <div>Fecha: {{ $informe->fecha_informe ? \Carbon\Carbon::parse($informe->fecha_informe)->format('d/m/Y') : '-' }}</div>
            <div>Tecnico: {{ $tecnico?->nombre_tecnico ?? '-' }}</div>
        </div>
    </div>

    <div class="sec">
        <h3>Resumen</h3>
        <table>
            <tr>
                <td width="33%"><span class="lbl">Cliente</span>{{ trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellidos ?? '')) ?: ($empresa->nombre ?? '-') }}</td>
                <td width="33%"><span class="lbl">Equipo</span>{{ trim(($equipo->tipo ?? '') . ' ' . ($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) ?: '-' }}</td>
                <td width="34%"><span class="lbl">Estado Final</span>{{ $informe->estado_equipo ?: '-' }}</td>
            </tr>
        </table>
    </div>

    @if($repuestosUsados->isNotEmpty())
    <div class="sec">
        <h3>Repuestos Utilizados</h3>
        <table>
            @foreach($repuestosUsados as $item)
                <tr>
                    <td width="25%"><span class="lbl">Código</span>{{ $item->repuesto?->codigo ?: '-' }}</td>
                    <td width="45%"><span class="lbl">Nombre</span>{{ $item->repuesto?->nombre ?: '-' }}</td>
                    <td width="20%"><span class="lbl">Nro. Parte</span>{{ $item->repuesto?->nro_parte ?: '-' }}</td>
                    <td width="10%"><span class="lbl">Cant.</span>{{ (int) ($item->cantidad ?: 1) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="sec">
        <h3>Antecedentes</h3>
        <div class="txt">{{ $informe->antecedentes ?: '-' }}</div>
    </div>

    <div class="sec">
        <h3>Proceso Tecnico</h3>
        <div class="txt">{{ $informe->proceso ?: '-' }}</div>
    </div>

    <div class="sec">
        <h3>Conclusion</h3>
        <div class="txt">{{ $informe->conclusion ?: '-' }}</div>
    </div>

    <div class="sec">
        <h3>Recomendaciones</h3>
        <div class="txt">{{ $informe->recomendaciones ?: '-' }}</div>
    </div>

    @if($informe->fotos->isNotEmpty())
    <div class="sec">
        <h3>Evidencia Fotografica</h3>
        <div class="fotos">
            @foreach($informe->fotos as $foto)
                @php
                    $rutaFoto = (string) ($foto->foto_data ?? '');
                    $src = str_starts_with($rutaFoto, 'data:') ? $rutaFoto : asset('storage/' . ltrim($rutaFoto, '/'));
                @endphp
                <div class="foto-card">
                    <img src="{{ $src }}" alt="Foto {{ $loop->iteration }}">
                    <div class="foto-cap">{{ $foto->caption ?: ($foto->nombre_archivo ?: 'Foto ' . $loop->iteration) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</body>
</html>
