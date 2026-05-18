@extends('layouts.app')

@section('titulo', 'Dashboard Principal')

@section('contenido')
    <div class="dashboard-header">
        <h1>Panel de Control SGN</h1>
    </div>

    <div class="dashboard-widgets">
        <div class="widget card-ordenes">
            <h3>Órdenes Activas</h3>
            <div class="widget-content" id="widget-ordenes-activas">
                <span class="loading-text">Cargando...</span>
            </div>
        </div>

        <div class="widget card-repuestos">
            <h3>Solicitudes Repuestos</h3>
            <div class="widget-content" id="widget-repuestos">
                <span class="loading-text">Cargando...</span>
            </div>
        </div>

    </div>
@endsection

@stack('js_adicional')
<script>
    // Se mantiene el comportamiento AJAX original para cargar el contenido
    // Solo cambia la URL apuntando a las nuevas rutas de Laravel
    $(document    // Implementacion JS legacy...
</script>
@endpush
