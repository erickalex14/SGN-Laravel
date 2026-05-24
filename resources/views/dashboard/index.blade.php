@extends('layouts.app')

@section('titulo', 'Panel de Control - SGN')

@push('css_adicional')
<style>
/* CSS profesional adaptado de la version original */
.dash-container { max-width: 1400px; margin: 0 auto; padding: 30px 24px; }
.dash-hdr { margin-bottom: 28px; }
.dash-hdr h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
.dash-hdr p { margin: 0; color: #64748b; font-size: 15px; }
.widget-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 30px; }
.widget-card { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s; }
.widget-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
.w-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 26px; }
.w-icon.blue { background: #eff6ff; color: #2563eb; }
.w-icon.green { background: #f0fdf4; color: #16a34a; }
.w-icon.orange { background: #fff7ed; color: #ea580c; }
.w-icon.red { background: #fef2f2; color: #dc2626; }
.w-info { text-align: right; }
.w-title { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.w-value { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; }
.w-value.loading { font-size: 16px; color: #94a3b8; font-weight: 600; }
</style>
@endpush

@section('contenido')
<div class="dash-container">
    <div class="dash-hdr">
        <h1>Panel de Control Principal</h1>
        <p>Resumen de indicadores operativos y tareas pendientes.</p>
    </div>

    <div class="widget-grid">
        <div class="widget-card">
            <div class="w-icon blue"><i class="bi bi-person-workspace"></i></div>
            <div class="w-info">
                <div class="w-title">Mis Órdenes Activas</div>
                <div class="w-value loading" id="kpi-mis-ordenes">Calculando...</div>
            </div>
        </div>

        <div class="widget-card">
            <div class="w-icon green"><i class="bi bi-check-circle"></i></div>
            <div class="w-info">
                <div class="w-title">Equipos Reparados (Mes)</div>
                <div class="w-value loading" id="kpi-reparados">Calculando...</div>
            </div>
        </div>

        @if(session('es_superadmin') || isset(session('permisos')['ordenes_asignadas']['ver']))
            <div class="widget-card">
                <div class="w-icon orange"><i class="bi bi-stack"></i></div>
                <div class="w-info">
                    <div class="w-title">Órdenes Globales Activas</div>
                    <div class="w-value loading" id="kpi-globales">Calculando...</div>
                </div>
            </div>

            <div class="widget-card">
                <div class="w-icon red"><i class="bi bi-box-seam"></i></div>
                <div class="w-info">
                    <div class="w-title">Tickets Bodega Pendientes</div>
                    <div class="w-value loading" id="kpi-repuestos">Calculando...</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('js_adicional')
<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarMetricas();
    // Refresco automatico cada 60 segundos
    setInterval(cargarMetricas, 60000);
});

function cargarMetricas() {
    fetch('{{ route("dashboard.metricas") }}')
        .then(response => {
            if (!response.ok) throw new Error('Respuesta de red no exitosa');
            return response.json();
        })
        .then(res => {
            if (res.ok && res.data) {
                actualizarElemento('kpi-mis-ordenes', res.data.mis_ordenes_activas);
                actualizarElemento('kpi-reparados', res.data.equipos_reparados_mes);
                
                if (res.data.ordenes_activas_globales !== undefined) {
                    actualizarElemento('kpi-globales', res.data.ordenes_activas_globales);
                }
                if (res.data.repuestos_pendientes !== undefined) {
                    actualizarElemento('kpi-repuestos', res.data.repuestos_pendientes);
                }
            } else {
                console.error('Error al procesar metricas:', res.error);
            }
        })
        .catch(error => {
            console.error('Fallo en la peticion de metricas:', error);
            document.querySelectorAll('.w-value.loading').forEach(el => {
                el.textContent = 'Error';
                el.style.color = '#ef4444';
            });
        });
}

function actualizarElemento(id, valor) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('loading');
        el.textContent = valor;
        // Efecto visual sutil de actualizacion
        el.style.opacity = '0.5';
        setTimeout(() => { el.style.opacity = '1'; }, 150);
    }
}
</script>
@endpush