@extends('layouts.app')

@section('titulo', 'Mis Actividades Diarias')

@php
    $now = \Carbon\Carbon::now('America/Guayaquil');
    $puedeEditar = $now->hour < 20;
@endphp

@push('css_adicional')
<style>
    .act-wrap { padding: 20px; }
    .act-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        padding: 24px;
        margin-bottom: 24px;
    }
    .act-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .act-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .act-filter-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .act-input {
        padding: 8px 14px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
        outline: none;
        transition: border-color 0.2s;
    }
    .act-input:focus { border-color: #2563eb; }
    .act-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .act-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 237, 0.2);
    }
    .act-btn-green {
        background: #16a34a;
    }
    .act-btn-green:hover {
        background: #15803d;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
    }
    .act-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .act-stat-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .act-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(37, 99, 237, 0.1);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .act-stat-info { display: flex; flex-direction: column; }
    .act-stat-val { font-size: 18px; font-weight: 800; color: #0f172a; }
    .act-stat-lbl { font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; }
    
    .act-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .act-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 13px;
        text-align: left;
    }
    .act-table th {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        padding: 12px 16px;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .act-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .act-table tr:hover { background: #f8fafc; }
    .act-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .act-badge-realizado { background: #dcfce7; color: #15803d; }
    .act-badge-pendiente { background: #fef9c3; color: #a16207; }
    .act-badge-proceso { background: #dbeafe; color: #1d4ed8; }
    .act-badge-sn { background: #f1f5f9; color: #64748b; }
    .act-badge-almuerzo { background: #fee2e2; color: #b91c1c; }
    
    .act-table select, .act-table input, .act-table textarea {
        width: 100%;
        padding: 5px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 12.5px;
        color: #334155;
        background-color: #ffffff;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .act-table select:focus, .act-table input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 237, 0.15);
    }
</style>
@endpush

@section('contenido')
<div class="act-wrap">
    <div class="act-card">
        <div class="act-header">
            <h2>
                <i class="bi bi-journal-check" style="color: #2563eb;"></i>
                Mis Actividades Diarias
            </h2>
            <div class="act-filter-row">
                <span class="badge bg-primary text-white p-2" style="font-size: 13.5px; font-weight: 700; border-radius: 8px;">
                    Hoy: {{ \Carbon\Carbon::parse($fechaHoy)->format('d/m/Y') }}
                </span>
                <a href="{{ route('actividades.historial') }}" class="act-btn" style="background: #475569;">
                    <i class="bi bi-calendar3"></i>
                    Ver Historial
                </a>
                @if($puedeEditar)
                    <button class="act-btn" id="btn-guardar" style="background: #2563eb;">
                        <i class="bi bi-save"></i>
                        Guardar Actividades de Hoy
                    </button>
                @else
                    <button class="act-btn" id="btn-guardar" style="background: #cbd5e1; color: #94a3b8; cursor: not-allowed;" disabled title="Edición cerrada a las 8:00 PM">
                        <i class="bi bi-lock-fill"></i>
                        Edición Cerrada (8:00 PM)
                    </button>
                @endif
                <button class="act-btn act-btn-green" id="btn-exportar">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Descargar Excel
                </button>
            </div>
        </div>

        @if(!$puedeEditar)
            <div class="alert d-flex align-items-center gap-2 mb-4" style="border-radius: 12px; background-color: #fffbeb; border: 1px solid #fde047; color: #854d0e; padding: 12px 16px; font-weight: 600; font-size: 13px;">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px; color: #d97706;"></i>
                <div>
                    La edición de actividades diarias del día de hoy está cerrada. Las modificaciones están permitidas únicamente hasta las 8:00 PM.
                </div>
            </div>
        @endif

        @if(session('tecnico_id') == 21 || (auth()->user() && auth()->user()->id == 21))
            <div class="act-card no-print" style="margin-bottom: 20px; padding: 16px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                <label style="font-weight: 700; color: #334155; display: block; margin-bottom: 10px; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.05em;">Modalidad de Trabajo de Hoy:</label>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1e293b; cursor: pointer;">
                        <input type="radio" name="modalidad_trabajo" value="oficina" checked onchange="cambiarModalidadTrabajo(this.value)" style="width: auto; cursor: pointer;">
                        <i class="bi bi-building me-1" style="color: #2563eb;"></i> Oficina / Laboratorio
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1e293b; cursor: pointer;">
                        <input type="radio" name="modalidad_trabajo" value="ticket_empresa" onchange="cambiarModalidadTrabajo(this.value)" style="width: auto; cursor: pointer;">
                        <i class="bi bi-ticket-detailed me-1" style="color: #10b981;"></i> Ticket a Empresa (9:00 a 18:00)
                    </label>
                </div>
            </div>
        @endif

        <div class="act-stats">
            <div class="act-stat-card">
                <div class="act-stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="act-stat-info">
                    <span class="act-stat-val" id="stat-horas">0/9</span>
                    <span class="act-stat-lbl">Horas Reportadas</span>
                </div>
            </div>
            <div class="act-stat-card">
                <div class="act-stat-icon" style="background: rgba(22, 163, 74, 0.1); color: #16a34a;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div class="act-stat-info">
                    <span class="act-stat-val" id="stat-acciones">0</span>
                    <span class="act-stat-lbl">Acciones Registradas</span>
                </div>
            </div>
            <div class="act-stat-card">
                <div class="act-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="act-stat-info">
                    <span class="act-stat-val" id="stat-ots">0</span>
                    <span class="act-stat-lbl">OTs Atendidas</span>
                </div>
            </div>
        </div>

        @if(session('tecnico_id') == 21 || (auth()->user() && auth()->user()->id == 21))
            <div id="vista-ticket-empresa" class="act-card" style="display: none; border: 1.5px solid #a7f3d0; background: #ffffff;">
                <h4 style="font-size: 15px; font-weight: 800; color: #065f46; margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-ticket-perforated-fill" style="color: #10b981; font-size: 18px;"></i> Reporte Diario - Ticket a Empresa
                </h4>
                <div style="margin-bottom: 18px; display: flex; gap: 20px; align-items: center; background: #ecfdf5; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; flex-wrap: wrap;">
                    <span style="font-size: 13px; font-weight: 700; color: #065f46;">
                        <i class="bi bi-clock me-1"></i> <strong>Horario de Jornada:</strong> 9:00 a 18:00
                    </span>
                    <span style="font-size: 13px; font-weight: 700; color: #065f46;">
                        <i class="bi bi-geo-alt me-1"></i> <strong>Modalidad:</strong> Presencial
                    </span>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Observaciones (Descripción de la actividad realizada en el día):</label>
                    <textarea id="ticket-descripcion" placeholder="Escribe de forma detallada las actividades que realizaste en el ticket o visita a la empresa..." style="width: 100%; min-height: 140px; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; resize: vertical; line-height: 1.5;" {{ $puedeEditar ? '' : 'disabled' }}></textarea>
                </div>
            </div>
        @endif

        <div class="act-table-container" style="overflow-x: auto;">
            <table class="act-table" id="tabla-actividades" style="min-width: {{ $esSistemas ? '600px' : '1250px' }};">
                <thead>
                    <tr>
                        <th style="width: 150px;">Horario</th>
                        @if(!$esSistemas)
                            <th style="width: 140px;">Actividad</th>
                            <th style="width: 130px;">Novedad</th>
                            <th style="width: 130px;">Estado</th>
                            <th style="width: 110px;">Modalidad</th>
                            <th style="width: 120px;">OT / Ticket</th>
                            <th style="width: 140px;">Clase</th>
                            <th style="width: 130px;">Serie</th>
                            <th style="width: 130px;">Código Equipo</th>
                        @endif
                        <th style="min-width: 350px;">Observaciones (Bitácora de Acciones)</th>
                    </tr>
                </thead>
                <tbody id="body-actividades">
                    <!-- Filas generadas por JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<!-- Cargar ExcelJS desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    const tecnicoNombre = @json($nombreTecnico);
    const puedeEditar = @json($puedeEditar);
    const esSistemas = @json($esSistemas);
    let actividadesRaw = [];

    document.addEventListener('DOMContentLoaded', function () {
        cargarActividades();
        const btnGuardar = document.getElementById('btn-guardar');
        if (btnGuardar && puedeEditar) {
            btnGuardar.addEventListener('click', guardarActividades);
        }
        document.getElementById('btn-exportar').addEventListener('click', exportarExcel);
    });

    // Definición de las 9 horas de la jornada
    const horasJornada = [
        { key: 9,  label: "9:00 a  10:00" },
        { key: 10, label: "10:00 a 11:00" },
        { key: 11, label: "11:00 a 12:00" },
        { key: 12, label: "12:00 a 13:00" },
        { key: 13, label: "13:00 a 14:00" },
        { key: 14, label: "14:00 a 15:00" },
        { key: 15, label: "15:00 a 16:00" },
        { key: 16, label: "16:00 a 17:00" },
        { key: 17, label: "17:00 a 18:00" }
    ];

    function cargarActividades() {
        const fecha = "{{ $fechaHoy }}";
        const container = document.getElementById('body-actividades');
        container.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Cargando reporte diario...</td></tr>';

        fetch(`{{ route('actividades.listar') }}?fecha=${fecha}`)
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    actividadesRaw = res.actividades;
                    renderizarTabla(fecha);
                } else {
                    container.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">${res.error}</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Error al obtener actividades.</td></tr>';
            });
    }

    function formatearBitacoraAutomatica(slotActs) {
        let lines = [];
        if (slotActs.length === 0) {
            return 'sn';
        }

        let groups = {};
        let others = [];
        slotActs.forEach(a => {
            let ot = a.metadata_json?.nro_orden;
            if (ot) {
                if (!groups[ot]) groups[ot] = [];
                groups[ot].push(a.descripcion.trim());
            } else {
                others.push(a.descripcion.trim());
            }
        });

        for (let ot in groups) {
            let uniqueDescs = [...new Set(groups[ot])];
            lines.push(`Orden #${ot}:\n  - ` + uniqueDescs.join('\n  - '));
        }

        others.forEach(desc => {
            lines.push(desc);
        });

        return lines.join('\n');
    }

    function parseHour(fechaHoraStr) {
        try {
            if (!fechaHoraStr) return 9;
            const str = String(fechaHoraStr).trim();
            if (str.includes(' ')) {
                const timePart = str.split(' ')[1];
                return parseInt(timePart.split(':')[0], 10);
            }
            if (str.includes('T')) {
                if (str.includes('Z') || str.includes('+') || (str.split('-').length > 3)) {
                    const d = new Date(str);
                    if (!isNaN(d.getTime())) {
                        return d.getHours();
                    }
                }
                const timePart = str.split('T')[1];
                return parseInt(timePart.split(':')[0], 10);
            }
            return parseInt(str, 10);
        } catch (e) {
            return 9;
        }
    }

    function mapClase(tipo) {
        if (!tipo) return 'sn';
        const t = tipo.toUpperCase().trim();
        if (t.includes('LAPTOP') || t.includes('PORTATIL') || t.includes('NOTEBOOK')) return 'LAPTOPS';
        if (t.includes('MONITOR') || t.includes('PANTALLA')) return 'MONITORES';
        if (t.includes('CELULAR') || t.includes('TELEFONO') || t.includes('IPHONE')) return 'CELULARES';
        if (t.includes('IMPRESORA') || t.includes('MULTIFUNCIONAL')) return 'IMPRESORAS';
        if (t.includes('TV') || t.includes('TELEVISOR') || t.includes('SMART TV')) return 'TVS';
        if (t.includes('MOTO')) return 'MOTOS';
        if (t.includes('CONSOLA') || t.includes('PLAYSTATION') || t.includes('NINTENDO') || t.includes('XBOX')) return 'CONSOLAS';
        if (t.includes('TABLET') || t.includes('IPAD')) return 'TABLETS ';
        if (t.includes('COMPUTADORA') || t.includes('ESCRITORIO') || t.includes('PC') || t.includes('CASE')) return 'PC';
        if (t.includes('AIO') || t.includes('ALL IN ONE')) return 'AIO';
        if (t.includes('ACCESORIO') || t.includes('TECLADO') || t.includes('MOUSE') || t.includes('AUDIFONOS')) return 'ACCESORIO';
        if (t.includes('GYM') || t.includes('TREADMILL') || t.includes('CAMINADORA') || t.includes('ELIPTICA')) return 'EQUIPO GYM';
        if (t.includes('BLANCA') || t.includes('REFRIGERADORA') || t.includes('LAVADORA') || t.includes('MICROONDAS')) return 'LINEA BLANCA';
        if (t.includes('JUGUETE')) return 'JUGUETES';
        if (t.includes('SOPORTE')) return 'SOPORTE';
        if (t.includes('SERVICIO')) return 'SERVICIO';
        if (t.includes('OFICINA')) return 'OFICINA';
        if (t.includes('HOGAR')) return 'HOGAR';
        if (t.includes('BICICLETA')) return 'BICICLETAS';
        return 'sn';
    }

    const optionsActividad = ["revision ", "reparacion", "instalacion ", "soporte", "ticket", "atencion", "almuerzo", "deligencia externa", "capacitacion ", "sn"];
    const optionsNovedad = ["tienda", "outlet", "incinerox", "autoconsumo", "garantia", "Oficina", "Empresa", "bodega", "servicio tecnico", "sn"];
    const optionsEstado = ["realizado ", "no realizado", "pendiente", "en proceso", "aprobado", "no aprobado", "nota credito", "sn"];
    const optionsModalidad = ["virtual", "presencial", "sn"];
    const optionsClase = ["LAPTOPS", "ACCESORIO", "EQUIPO GYM", "LINEA BLANCA", "MONITORES", "JUGUETES", "SOPORTE", "SERVICIO", "PC", "AIO", "CELULARES", "IMPRESORAS", "TVS", "MOTOS", "CONSOLAS", "OFICINA", "HOGAR", "BICICLETAS", "TABLETS ", "sn"];

    function buildSelectHtml(name, options, currentValue, slotKey) {
        const disabledAttr = puedeEditar ? '' : 'disabled';
        let html = `<select name="${name}" data-slot="${slotKey}" ${disabledAttr}>`;
        options.forEach(opt => {
            const selected = (currentValue.trim() === opt.trim()) ? 'selected' : '';
            html += `<option value="${opt}" ${selected}>${opt}</option>`;
        });
        html += `</select>`;
        return html;
    }

    function renderizarTabla(fecha) {
        const container = document.getElementById('body-actividades');
        container.innerHTML = '';

        let totalAcciones = actividadesRaw.length;
        let totalOts = 0;
        let horasTrabajadas = 0;
        const otsSet = new Set();

        horasJornada.forEach(slot => {
            let slotActs = actividadesRaw.filter(act => {
                const hour = parseHour(act.fecha_hora);
                if (slot.key === 9) return hour <= 9;
                if (slot.key === 17) return hour >= 17;
                return hour === slot.key;
            });

            let valActividad = 'sn';
            let valNovedad = 'Oficina';
            let valEstado = 'realizado ';
            let valModalidad = 'presencial';
            let ots = 'sn';
            let clase = 'sn';
            let serie = 'sn';
            let codigoEquipo = 'sn';
            let observaciones = 'sn';

            let hasWork = false;
            const manualAct = slotActs.find(a => a.tipo_accion === 'registro_manual');
            const autoActs = slotActs.filter(a => a.tipo_accion !== 'registro_manual');
            const hasRealManual = manualAct && manualAct.descripcion && manualAct.descripcion.trim() !== '' && manualAct.descripcion.trim() !== 'sn';

            if (autoActs.length > 0) {
                hasWork = true;
                const autoBitacora = formatearBitacoraAutomatica(autoActs);

                if (hasRealManual && !autoBitacora.includes(manualAct.descripcion.trim())) {
                    observaciones = autoBitacora + "\n\nNota técnica adicional:\n" + manualAct.descripcion.trim();
                } else if (hasRealManual) {
                    observaciones = manualAct.descripcion.trim();
                } else {
                    observaciones = autoBitacora;
                }

                const otsEnSlot = autoActs.map(a => a.metadata_json?.nro_orden).filter(Boolean);
                if (otsEnSlot.length > 0) {
                    ots = [...new Set(otsEnSlot)].join(', ');
                    otsEnSlot.forEach(o => otsSet.add(o));
                }

                const mainAct = autoActs.find(a => a.metadata_json?.nro_orden);
                if (mainAct) {
                    clase = mapClase(mainAct.metadata_json?.tipo);
                    serie = mainAct.metadata_json?.serie || 'sn';
                    codigoEquipo = mainAct.metadata_json?.codigo_equipo || 'sn';
                    
                    if (mainAct.tipo_accion.includes('crear') || mainAct.tipo_accion.includes('ingresar')) {
                        valActividad = 'ticket';
                    } else if (mainAct.tipo_accion.includes('estado')) {
                        valActividad = 'reparacion';
                    } else {
                        valActividad = 'ticket';
                    }

                    if (mainAct.metadata_json?.estado_garantia && mainAct.metadata_json?.estado_garantia.toUpperCase() !== 'NO APLICA') {
                        valNovedad = 'garantia';
                    } else {
                        valNovedad = 'Oficina';
                    }

                    const est = mainAct.metadata_json?.estado_orden ? mainAct.metadata_json?.estado_orden.toUpperCase() : '';
                    if (est.includes('PROCESO')) {
                        valEstado = 'en proceso';
                    } else if (est.includes('PENDIENTE')) {
                        valEstado = 'pendiente';
                    } else if (est.includes('NOTA') || est.includes('CREDITO')) {
                        valEstado = 'nota credito';
                    } else {
                        valEstado = 'realizado ';
                    }
                } else {
                    valActividad = 'ticket';
                    valNovedad = 'Oficina';
                    valEstado = 'realizado ';
                }

                if (manualAct && manualAct.metadata_json) {
                    const meta = manualAct.metadata_json;
                    if (meta.actividad && meta.actividad !== 'sn') valActividad = meta.actividad;
                    if (meta.novedad && meta.novedad !== 'sn') valNovedad = meta.novedad;
                    if (meta.estado && meta.estado !== 'sn') valEstado = meta.estado;
                    if (meta.modalidad && meta.modalidad !== 'sn') valModalidad = meta.modalidad;
                    if (meta.ot && meta.ot !== 'sn') ots = meta.ot;
                    if (meta.clase && meta.clase !== 'sn') clase = meta.clase;
                    if (meta.serie && meta.serie !== 'sn') serie = meta.serie;
                    if (meta.codigo_equipo && meta.codigo_equipo !== 'sn') codigoEquipo = meta.codigo_equipo;
                }
            } else if (manualAct) {
                const meta = manualAct.metadata_json || {};
                valActividad = meta.actividad || 'sn';
                valNovedad = meta.novedad || 'sn';
                valEstado = meta.estado || 'sn';
                valModalidad = meta.modalidad || 'presencial';
                ots = meta.ot || 'sn';
                clase = meta.clase || 'sn';
                serie = meta.serie || 'sn';
                codigoEquipo = meta.codigo_equipo || 'sn';
                observaciones = manualAct.descripcion || 'sn';
                if (valActividad !== 'sn' && valActividad !== 'almuerzo') {
                    hasWork = true;
                }
                if (ots && ots !== 'sn') {
                    ots.split(',').map(o => o.trim()).filter(Boolean).forEach(o => otsSet.add(o));
                }
            }

            if (esSistemas) {
                valActividad = 'ticket';
                valNovedad = 'sn';
                valEstado = 'sn';
                valModalidad = 'sn';
                ots = 'sn';
                clase = 'sn';
                serie = 'sn';
                if (manualAct) {
                    observaciones = manualAct.descripcion || 'sn';
                }
            }

            let rowHtml = '';
            if (esSistemas) {
                rowHtml = `
                    <tr data-row-slot="${slot.key}">
                        <td style="font-weight: 700; color: #475569; vertical-align: middle;">${slot.label}</td>
                        <input type="hidden" name="actividad" value="${valActividad}">
                        <input type="hidden" name="novedad" value="${valNovedad}">
                        <input type="hidden" name="estado" value="${valEstado}">
                        <input type="hidden" name="modalidad" value="${valModalidad}">
                        <input type="hidden" name="ot" value="${ots}">
                        <input type="hidden" name="clase" value="${clase}">
                        <input type="hidden" name="serie" value="${serie}">
                        <input type="hidden" name="codigo_equipo" value="${codigoEquipo}">
                        <td style="vertical-align: middle;"><textarea name="observacion" placeholder="Escribe tu actividad aquí..." ${puedeEditar ? '' : 'disabled'} style="resize: vertical; min-height: 48px; line-height: 1.4;">${observaciones === 'sn' ? '' : observaciones}</textarea></td>
                    </tr>
                `;
            } else {
                rowHtml = `
                    <tr data-row-slot="${slot.key}">
                        <td style="font-weight: 700; color: #475569; vertical-align: middle;">${slot.label}</td>
                        <td style="vertical-align: middle;">${buildSelectHtml('actividad', optionsActividad, valActividad, slot.key)}</td>
                        <td style="vertical-align: middle;">${buildSelectHtml('novedad', optionsNovedad, valNovedad, slot.key)}</td>
                        <td style="vertical-align: middle;">${buildSelectHtml('estado', optionsEstado, valEstado, slot.key)}</td>
                        <td style="vertical-align: middle;">${buildSelectHtml('modalidad', optionsModalidad, valModalidad, slot.key)}</td>
                        <td style="vertical-align: middle;"><input type="text" name="ot" value="${ots === 'sn' ? '' : ots}" placeholder="sn" ${puedeEditar ? '' : 'disabled'} onchange="buscarDetallesOt(this)"></td>
                        <td style="vertical-align: middle;">${buildSelectHtml('clase', optionsClase, clase, slot.key)}</td>
                        <td style="vertical-align: middle;"><input type="text" name="serie" value="${serie === 'sn' ? '' : serie}" placeholder="sn" ${puedeEditar ? '' : 'disabled'}></td>
                        <td style="vertical-align: middle;"><input type="text" name="codigo_equipo" value="${codigoEquipo === 'sn' ? '' : codigoEquipo}" placeholder="sn" ${puedeEditar ? '' : 'disabled'}></td>
                        <td style="vertical-align: middle;"><textarea name="observacion" placeholder="sn" ${puedeEditar ? '' : 'disabled'} style="resize: vertical; min-height: 48px; line-height: 1.4;">${observaciones === 'sn' ? '' : observaciones}</textarea></td>
                    </tr>
                `;
            }
            if (hasWork) {
                horasTrabajadas++;
            }
            container.innerHTML += rowHtml;
        });

        document.getElementById('stat-horas').textContent = `${horasTrabajadas}/8`;
        document.getElementById('stat-acciones').textContent = totalAcciones;
        document.getElementById('stat-ots').textContent = otsSet.size;

        const isPucha = @json(session('tecnico_id') == 21 || (auth()->user() && auth()->user()->id == 21));
        if (isPucha) {
            let esTicketDetectado = false;
            const manualAct9 = actividadesRaw.find(a => a.tipo_accion === 'registro_manual' && parseHour(a.fecha_hora) === 9);
            if (manualAct9 && manualAct9.metadata_json?.novedad === 'Empresa') {
                esTicketDetectado = true;
            }

            if (esTicketDetectado) {
                const radio = document.querySelector('input[name="modalidad_trabajo"][value="ticket_empresa"]');
                if (radio) {
                    radio.checked = true;
                    cambiarModalidadTrabajo('ticket_empresa');
                }
                const descArea = document.getElementById('ticket-descripcion');
                if (descArea && manualAct9) {
                    descArea.value = manualAct9.descripcion || '';
                }
            } else {
                const radio = document.querySelector('input[name="modalidad_trabajo"][value="oficina"]');
                if (radio) {
                    radio.checked = true;
                    cambiarModalidadTrabajo('oficina');
                }
            }
        }
    }

    window.cambiarModalidadTrabajo = function(val) {
        const tableContainer = document.querySelector('.act-table-container');
        const vistaTicket = document.getElementById('vista-ticket-empresa');
        if (val === 'ticket_empresa') {
            if (tableContainer) tableContainer.style.display = 'none';
            if (vistaTicket) vistaTicket.style.display = 'block';
        } else {
            if (tableContainer) tableContainer.style.display = 'block';
            if (vistaTicket) vistaTicket.style.display = 'none';
        }
    };

    function guardarActividades() {
        const payload = {
            fecha: "{{ $fechaHoy }}",
            actividades: {}
        };

        const isPucha = @json(session('tecnico_id') == 21 || (auth()->user() && auth()->user()->id == 21));
        const esTicketEmpresa = isPucha && (document.querySelector('input[name="modalidad_trabajo"]:checked')?.value === 'ticket_empresa');

        if (esTicketEmpresa) {
            const desc = document.getElementById('ticket-descripcion').value.trim();
            if (!desc) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Descripción Requerida',
                    text: 'Por favor escribe la descripción de las actividades realizadas.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            horasJornada.forEach(slot => {
                const key = slot.key;
                payload.actividades[key] = {
                    actividad: 'ticket',
                    novedad: 'Empresa',
                    estado: 'realizado ',
                    modalidad: 'presencial',
                    ot: 'sn',
                    clase: 'sn',
                    serie: 'sn',
                    codigo_equipo: 'sn',
                    observacion: desc
                };
            });
        } else {
            horasJornada.forEach(slot => {
                const key = slot.key;
                const row = document.querySelector(`[data-row-slot="${key}"]`);
                if (row) {
                    payload.actividades[key] = {
                        actividad: row.querySelector('[name="actividad"]').value,
                        novedad: row.querySelector('[name="novedad"]').value,
                        estado: row.querySelector('[name="estado"]').value,
                        modalidad: row.querySelector('[name="modalidad"]').value,
                        ot: row.querySelector('[name="ot"]').value,
                        clase: row.querySelector('[name="clase"]').value,
                        serie: row.querySelector('[name="serie"]').value,
                        codigo_equipo: row.querySelector('[name="codigo_equipo"]').value,
                        observacion: row.querySelector('[name="observacion"]').value
                    };
                }
            });
        }

        const btn = document.getElementById('btn-guardar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        fetch("{{ route('actividades.guardar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado exitoso',
                    text: res.mensaje,
                    confirmButtonColor: '#2563eb',
                    timer: 2000
                });
                cargarActividades();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.error || 'No se pudieron guardar las actividades.',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error de Red',
                text: 'Hubo un problema al conectar con el servidor.',
                confirmButtonColor: '#ef4444'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar Actividades de Hoy';
        });
    }

    async function exportarExcel() {
        if (!actividadesRaw.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Reporte sin datos',
                text: 'No tienes actividades registradas para este día, el excel estará vacío.',
                confirmButtonColor: '#2563eb'
            });
        }

        const fecha = "{{ $fechaHoy }}";
        const btn = document.getElementById('btn-exportar');
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm" role="status"></i> Generando...';

        try {
            const wb = new ExcelJS.Workbook();
            wb.creator = 'SGN - Novitecnologia';
            wb.created = new Date();

            const dateParts = fecha.split('-');
            const dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            const meses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            const mesNombre = meses[dateObj.getMonth()];
            const sheetName = `ACTIVIDADES DIARIAS MES ${mesNombre}`;

            const ws = wb.addWorksheet(sheetName, {
                views: [{ showGridLines: true }]
            });

            const borderStyle = {
                top: { style: 'thin', color: { argb: 'FFD1D5DB' } },
                left: { style: 'thin', color: { argb: 'FFD1D5DB' } },
                bottom: { style: 'thin', color: { argb: 'FFD1D5DB' } },
                right: { style: 'thin', color: { argb: 'FFD1D5DB' } }
            };

            ws.columns = [
                { width: 22 }, // A: Fecha
                { width: 15 }, // B: Horario
                { width: 30 }, // C: Actividad
                { width: 20 }, // D: Novedad
                { width: 16 }, // E: Estado
                { width: 14 }, // F: Modalidad
                { width: 26 }, // G: Tecnico
                { width: 18 }, // H: OT o Ticket
                { width: 10 }, // I: Cantidad
                { width: 16 }, // J: Codigo equipo
                { width: 18 }, // K: Clase
                { width: 22 }, // L: Serie equipo
                { width: 65 }, // M: Observacion
                { width: 35 }  // N: Codigo repuesto
            ];

            const formattedHeaderDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
            const headers = [
                `FECHA F: ${formattedHeaderDate}`,
                'HORARIO ',
                'ACTIVIDAD/DETALLE PRODUCTO ',
                'NOVEDAD ',
                'ESTADO ',
                'MODALIDAD ',
                'TECNICO RESPONSABLE ',
                'OT O TICKET ',
                'CANTIDAD',
                'CODIGO EQUIPO ',
                'CLASE ',
                'SERIE EQUIPO',
                'OBSERVACION',
                'CODIGO REPUESTO UTILIZADO EN OT DE GARANTIA'
            ];

            const headerRow = ws.addRow(headers);
            headerRow.height = 24;
            headerRow.eachCell((cell, colNum) => {
                cell.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FF0F172A' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
                cell.alignment = { horizontal: colNum === 1 || colNum === 13 ? 'left' : 'center', vertical: 'middle' };
                cell.border = borderStyle;
            });

            horasJornada.forEach((slot, idx) => {
                let slotActs = actividadesRaw.filter(act => {
                    const hour = parseHour(act.fecha_hora);
                    if (slot.key === 9) return hour <= 9;
                    if (slot.key === 17) return hour >= 17;
                    return hour === slot.key;
                });

                let valActividad = 'sn';
                let valNovedad = 'sn';
                let valEstado = 'sn';
                let valModalidad = 'presencial';
                let ots = 'sn';
                let clase = 'sn';
                let serie = 'sn';
                let observaciones = 'sn';
                let repuestoCode = 'sn';
                let equipoCode = 'sn';

                // Sin almuerzo automatico

                const manualAct = slotActs.find(a => a.tipo_accion === 'registro_manual');
                const autoActs = slotActs.filter(a => a.tipo_accion !== 'registro_manual');
                const hasRealManual = manualAct && manualAct.descripcion && manualAct.descripcion.trim() !== '' && manualAct.descripcion.trim() !== 'sn';

                if (autoActs.length > 0) {
                    const autoBitacora = formatearBitacoraAutomatica(autoActs);

                    if (hasRealManual && !autoBitacora.includes(manualAct.descripcion.trim())) {
                        observaciones = autoBitacora + "\n\nNota técnica adicional:\n" + manualAct.descripcion.trim();
                    } else if (hasRealManual) {
                        observaciones = manualAct.descripcion.trim();
                    } else {
                        observaciones = autoBitacora;
                    }

                    const otsEnSlot = autoActs.map(a => a.metadata_json?.nro_orden).filter(Boolean);
                    if (otsEnSlot.length > 0) {
                        ots = [...new Set(otsEnSlot)].join(', ');
                    }

                    const mainAct = autoActs.find(a => a.metadata_json?.nro_orden);
                    if (mainAct) {
                        clase = mapClase(mainAct.metadata_json?.tipo);
                        serie = mainAct.metadata_json?.serie || 'sn';
                        equipoCode = mainAct.metadata_json?.codigo_equipo || 'sn';
                        
                        if (mainAct.tipo_accion.includes('crear') || mainAct.tipo_accion.includes('ingresar')) {
                            valActividad = 'ticket';
                        } else if (mainAct.tipo_accion.includes('estado')) {
                            valActividad = 'reparacion';
                        } else {
                            valActividad = 'ticket';
                        }

                        if (mainAct.metadata_json?.estado_garantia && mainAct.metadata_json?.estado_garantia.toUpperCase() !== 'NO APLICA') {
                            valNovedad = 'garantia';
                        } else {
                            valNovedad = 'Oficina';
                        }

                        const est = mainAct.metadata_json?.estado_orden ? mainAct.metadata_json?.estado_orden.toUpperCase() : '';
                        if (est.includes('PROCESO')) {
                            valEstado = 'en proceso';
                        } else if (est.includes('PENDIENTE')) {
                            valEstado = 'pendiente';
                        } else if (est.includes('NOTA') || est.includes('CREDITO')) {
                            valEstado = 'nota credito';
                        } else {
                            valEstado = 'realizado ';
                        }

                        if (mainAct.metadata_json?.repuesto_inventario_id) {
                            repuestoCode = mainAct.metadata_json?.repuesto_inventario_id;
                        }
                    } else {
                        valActividad = 'ticket';
                        valNovedad = 'Oficina';
                        valEstado = 'realizado ';
                    }

                    if (manualAct && manualAct.metadata_json) {
                        const meta = manualAct.metadata_json;
                        if (meta.actividad && meta.actividad !== 'sn') valActividad = meta.actividad;
                        if (meta.novedad && meta.novedad !== 'sn') valNovedad = meta.novedad;
                        if (meta.estado && meta.estado !== 'sn') valEstado = meta.estado;
                        if (meta.modalidad && meta.modalidad !== 'sn') valModalidad = meta.modalidad;
                        if (meta.ot && meta.ot !== 'sn') ots = meta.ot;
                        if (meta.clase && meta.clase !== 'sn') clase = meta.clase;
                        if (meta.serie && meta.serie !== 'sn') serie = meta.serie;
                        if (meta.codigo_equipo && meta.codigo_equipo !== 'sn') equipoCode = meta.codigo_equipo;
                        if (meta.codigo_repuesto && meta.codigo_repuesto !== 'sn') repuestoCode = meta.codigo_repuesto;
                    }
                } else if (manualAct) {
                    const meta = manualAct.metadata_json || {};
                    valActividad = meta.actividad || 'sn';
                    valNovedad = meta.novedad || 'sn';
                    valEstado = meta.estado || 'sn';
                    valModalidad = meta.modalidad || 'presencial';
                    ots = meta.ot || 'sn';
                    clase = meta.clase || 'sn';
                    serie = meta.serie || 'sn';
                    observaciones = manualAct.descripcion || 'sn';
                    repuestoCode = meta.codigo_repuesto || 'sn';
                    equipoCode = meta.codigo_equipo || 'sn';
                }

                if (esSistemas) {
                    valActividad = 'ticket';
                    valNovedad = 'sn';
                    valEstado = 'sn';
                    valModalidad = 'sn';
                    ots = 'sn';
                    clase = 'sn';
                    serie = 'sn';
                    equipoCode = 'sn';
                    repuestoCode = 'sn';
                    if (manualAct) {
                        observaciones = manualAct.descripcion || 'sn';
                    }
                }

                const excelRowValues = [
                    dateObj,
                    slot.label,
                    valActividad,
                    valNovedad,
                    valEstado,
                    valModalidad,
                    tecnicoNombre,
                    ots,
                    ots !== 'sn' ? [...new Set(ots.split(', '))].length : 'sn',
                    equipoCode,
                    clase,
                    serie,
                    observaciones,
                    repuestoCode
                ];

                const row = ws.addRow(excelRowValues);
                row.height = 20;
                row.eachCell((cell, colNum) => {
                    cell.font = { name: 'Calibri', size: 11, bold: false };
                    cell.border = borderStyle;
                    
                    if (colNum === 1) {
                        cell.numFormat = 'yyyy-mm-dd';
                        cell.alignment = { horizontal: 'center', vertical: 'middle' };
                    } else if (colNum === 13) {
                        cell.alignment = { horizontal: 'left', vertical: 'middle', wrapText: true };
                    } else {
                        cell.alignment = { horizontal: 'center', vertical: 'middle' };
                    }
                });
            });

            ws.addRow([]);
            const commitRow = ws.addRow([]);
            commitRow.getCell(13).value = 'Comits del dia de hoy:';
            commitRow.getCell(13).font = { name: 'Calibri', size: 11, bold: true };

            const wsBase = wb.addWorksheet('HOJA BASE ', { views: [{ showGridLines: true }] });
            wsBase.columns = [
                { width: 5 },
                { width: 15 },
                { width: 25 },
                { width: 20 },
                { width: 16 },
                { width: 14 },
                { width: 26 },
                { width: 5 },
                { width: 5 },
                { width: 5 },
                { width: 20 }
            ];

            const baseHeaders = ['', 'HORARIO ', 'ACTIVIDAD/DETALLE PRODUCTO ', 'NOVEDAD ', 'ESTADO ', 'MODALIDAD ', 'TECNICO RESPONSABLE ', '', '', '', 'CLASE'];
            const baseHeaderRow = wsBase.addRow(baseHeaders);
            baseHeaderRow.eachCell((cell, colNum) => {
                if (colNum > 1 && cell.value) {
                    cell.font = { name: 'Calibri', size: 11, bold: true };
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };
                    cell.border = borderStyle;
                }
            });

            const options = {
                B: ["9:00 a  10:00", "10:00 a 11:00", "11:00 a 12:00", "12:00 a 13:00", "13:00 a 14:00", "14:00 a 15:00", "15:00 a 16:00", "16:00 a 17:00", "17:00 a 18:00", "9:00 a 18:00"],
                C: ["revision ", "reparacion", "instalacion ", "soporte", "ticket", "atencion", "almuerzo", "deligencia externa", "capacitacion ", "sn"],
                D: ["tienda", "outlet", "incinerox", "autoconsumo", "garantia", "Oficina", "Empresa", "bodega", "servicio tecnico", "sn"],
                E: ["realizado ", "no realizado", "pendiente", "en proceso", "aprobado", "no aprobado", "nota credito", "sn"],
                F: ["virtual", "presencial", "sn"],
                G: ["ERICK MINA", "FRANKLIN BASANTES", "OMAR ALMEIDA", "JIMMY BALCAZAR", "JOSE PUCHA ", "LUIS MORALES ", "FRANKLIN RUIZ ", "JOSUE ROMERO ", "ALEJANDRO YEPEZ ", "ALEXANDER CHAVARREA "],
                K: ["LAPTOPS", "ACCESORIO", "EQUIPO GYM", "LINEA BLANCA", "MONITORES", "JUGUETES", "SOPORTE", "SERVICIO", "PC", "AIO", "CELULARES", "IMPRESORAS", "TVS", "MOTOS", "CONSOLAS", "OFICINA", "HOGAR", "BICICLETAS", "TABLETS ", "sn"]
            };

            const maxOptionsLength = Math.max(
                options.B.length, options.C.length, options.D.length,
                options.E.length, options.F.length, options.G.length,
                options.K.length
            );

            for (let r = 0; r < maxOptionsLength; r++) {
                const rowData = [
                    '',
                    options.B[r] || '',
                    options.C[r] || '',
                    options.D[r] || '',
                    options.E[r] || '',
                    options.F[r] || '',
                    options.G[r] || '',
                    '',
                    '',
                    '',
                    options.K[r] || ''
                ];
                const baseRow = wsBase.addRow(rowData);
                baseRow.eachCell((cell, colNum) => {
                    if (colNum > 1 && cell.value) {
                        cell.font = { name: 'Calibri', size: 11 };
                        cell.border = borderStyle;
                    }
                });
            }

            for (let r = 2; r <= 10; r++) {
                ws.getCell(`C${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$C$2:$C$11"]
                };
                ws.getCell(`D${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$D$2:$D$11"]
                };
                ws.getCell(`E${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$E$2:$E$9"]
                };
                ws.getCell(`F${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$F$2:$F$4"]
                };
                ws.getCell(`G${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$G$2:$G$11"]
                };
                ws.getCell(`K${r}`).dataValidation = {
                    type: 'list',
                    allowBlank: true,
                    formulae: ["'HOJA BASE '!$K$2:$K$20"]
                };
            }

            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Reporte Actividades ${tecnicoNombre} ${fecha.split('-').reverse().join('-')}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Descarga Exitosa',
                text: 'Tu reporte de actividades diarias se ha generado correctamente.',
                confirmButtonColor: '#2563eb',
                timer: 2500
            });
        } catch (e) {
            console.error(e);
            Swal.fire({
                icon: 'error',
                title: 'Error de Generación',
                text: 'Hubo un error al generar el archivo Excel: ' + e.message,
                confirmButtonColor: '#ef4444'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-excel-fill"></i> Descargar Excel';
        }
    }

    function buscarDetallesOt(inputElement) {
        const otVal = inputElement.value.trim();
        if (!otVal || otVal === 'sn') return;

        const row = inputElement.closest('tr');
        if (!row) return;

        fetch(`{{ route('actividades.buscar_ot') }}?ot=${otVal}`)
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    const selectClase = row.querySelector('[name="clase"]');
                    const inputSerie = row.querySelector('[name="serie"]');
                    const inputCodigo = row.querySelector('[name="codigo_equipo"]');

                    if (selectClase) {
                        const mapped = mapClase(res.clase);
                        selectClase.value = mapped;
                    }
                    if (inputSerie) {
                        inputSerie.value = res.serie === 'sn' ? '' : res.serie;
                    }
                    if (inputCodigo) {
                        inputCodigo.value = res.codigo_equipo === 'sn' ? '' : res.codigo_equipo;
                    }
                }
            })
            .catch(err => console.error('Error fetching OT details:', err));
    }
</script>
@endpush
