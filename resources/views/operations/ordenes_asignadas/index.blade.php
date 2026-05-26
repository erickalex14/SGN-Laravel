@extends('layouts.app')
@section('titulo', 'Ordenes Asignadas')

@push('css_adicional')
<style>
.oa-container{max-width:1100px;margin:0 auto;padding:28px 24px;}
.oa-global-empty{text-align:center;color:#94a3b8;padding:40px;font-size:14px;}
.oa-tecnico-bloque{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:18px;overflow:hidden;}
.oa-tec-header{display:flex;align-items:center;gap:14px;padding:16px 20px;cursor:pointer;background:#f8fafc;border-bottom:1px solid #e2e8f0;}
.oa-tec-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:16px;font-weight:800;display:flex;align-items:center;justify-content:center;}
.oa-tec-nombre{font-size:15px;font-weight:700;color:#0f172a;flex:1;}
.oa-badge-asig,.oa-badge-entr{padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
.oa-badge-asig{background:#dbeafe;color:#1e40af;}
.oa-badge-entr{background:#dcfce7;color:#166534;}
.oa-tec-body{display:none;padding:16px;}
.oa-tec-body.open{display:block;}
.oa-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:14px;padding-top:10px;}
.oa-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.oa-card-top{display:flex;align-items:center;justify-content:space-between;padding:12px 14px 8px;}
.oa-nro{font-family:monospace;font-weight:800;font-size:14px;color:#2563eb;}
.oa-cliente{padding:0 14px 4px;font-size:13px;font-weight:600;color:#0f172a;}
.oa-equipo{padding:0 14px 4px;font-size:12px;color:#475569;}
.oa-meta-row{padding:6px 14px 10px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;border-top:1px solid #f1f5f9;}
.oa-meta{font-size:11.5px;color:#64748b;}
.btn-det{display:inline-flex;align-items:center;justify-content:center;gap:5px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:7px;padding:6px 10px;font-size:12px;font-weight:600;text-decoration:none;}
.oa-empty{color:#94a3b8;font-size:13px;padding:16px;text-align:center;}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="oa-container">
    <div class="form-titulo">
        <h2><i class="bi bi-person-check me-2"></i>Ordenes Asignadas</h2>
        <p>Vista de ordenes agrupadas por tecnico</p>
    </div>

    @if(count($porTecnico) === 0)
        <div class="oa-global-empty">No hay ordenes asignadas actualmente.</div>
    @else
        @foreach($porTecnico as $idx => $pack)
            @php
                $tec = $pack['tecnico'];
                $enCurso = $pack['en_curso'];
                $entregadas = $pack['entregadas'];
            @endphp
            <div class="oa-tecnico-bloque">
                <div class="oa-tec-header" onclick="toggleTecnico('tec-{{ $idx }}')">
                    <div class="oa-tec-avatar">{{ strtoupper(substr($tec->nombre_tecnico, 0, 1)) }}</div>
                    <span class="oa-tec-nombre">{{ $tec->nombre_tecnico }}</span>
                    <span class="oa-badge-asig">{{ count($enCurso) }} en curso</span>
                    <span class="oa-badge-entr">{{ count($entregadas) }} entregadas</span>
                </div>
                <div class="oa-tec-body" id="tec-{{ $idx }}">
                    <h5 style="font-size:13px;color:#1e40af;margin:4px 0 8px;">Ordenes Asignadas</h5>
                    <div class="oa-cards-grid">
                        @forelse($enCurso as $o)
                            <div class="oa-card">
                                <div class="oa-card-top"><span class="oa-nro">{{ $o->nro_orden }}</span><span>{{ $o->estado_orden }}</span></div>
                                <div class="oa-cliente">{{ $o->cliente }}</div>
                                <div class="oa-equipo">{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }}</div>
                                <div class="oa-meta-row">
                                    <span class="oa-meta">{{ \Carbon\Carbon::parse($o->fecha_de_ingreso)->format('d/m/Y H:i') }}</span>
                                    <a class="btn-det" href="{{ url('/operaciones/ordenes/editar/'.$o->orden_id) }}"><i class="bi bi-eye"></i>Ver detalle</a>
                                </div>
                            </div>
                        @empty
                            <div class="oa-empty">Sin ordenes en esta seccion.</div>
                        @endforelse
                    </div>

                    <h5 style="font-size:13px;color:#166534;margin:14px 0 8px;">Ordenes Entregadas</h5>
                    <div class="oa-cards-grid">
                        @forelse($entregadas as $o)
                            <div class="oa-card">
                                <div class="oa-card-top"><span class="oa-nro">{{ $o->nro_orden }}</span><span>{{ $o->estado_orden }}</span></div>
                                <div class="oa-cliente">{{ $o->cliente }}</div>
                                <div class="oa-equipo">{{ trim(($o->tipo ?? '').' '.($o->marca ?? '').' '.($o->modelo ?? '')) }}</div>
                                <div class="oa-meta-row">
                                    <span class="oa-meta">{{ \Carbon\Carbon::parse($o->fecha_de_ingreso)->format('d/m/Y H:i') }}</span>
                                    <a class="btn-det" href="{{ url('/operaciones/ordenes/editar/'.$o->orden_id) }}"><i class="bi bi-eye"></i>Ver detalle</a>
                                </div>
                            </div>
                        @empty
                            <div class="oa-empty">Sin ordenes en esta seccion.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
</section>
@endsection

@push('js_adicional')
<script>
function toggleTecnico(id) {
    var body = document.getElementById(id);
    if (!body) return;
    body.classList.toggle('open');
}
</script>
@endpush

