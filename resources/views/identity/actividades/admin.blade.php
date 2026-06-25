@extends('layouts.app')

@section('titulo', 'Control de Actividades de Técnicos')

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
    .act-btn:hover:not(:disabled) {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 237, 0.2);
    }
    .act-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        opacity: 0.7;
    }
    .act-btn-green {
        background: #16a34a;
    }
    .act-btn-green:hover:not(:disabled) {
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
</style>
@endpush

@section('contenido')
<div class="act-wrap">
    <div class="act-card">
        <div class="act-header">
            <h2>
                <i class="bi bi-people-fill" style="color: #2563eb;"></i>
                Control de Actividades de Técnicos
            </h2>
            <div class="act-filter-row">
                <label for="tecnico-filtro" style="font-size: 13.5px; font-weight: 700; color: #475569;">Técnico:</label>
                <select id="tecnico-filtro" class="act-input">
                    <option value="">-- Seleccione un Técnico --</option>
                    @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                    @endforeach
                </select>

                <label for="fecha-filtro" style="font-size: 13.5px; font-weight: 700; color: #475569;">Fecha:</label>
                <input type="date" id="fecha-filtro" class="act-input" value="{{ $fechaHoy }}">

                <button class="act-btn act-btn-green" id="btn-exportar" disabled>
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Descargar Excel
                </button>
            </div>
        </div>

        <div class="act-stats">
            <div class="act-stat-card">
                <div class="act-stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="act-stat-info">
                    <span class="act-stat-val" id="stat-horas">0/8</span>
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

        <div class="act-table-container">
            <table class="act-table" id="tabla-actividades">
                <thead>
                    <tr>
                        <th style="width: 140px;">Horario</th>
                        <th style="width: 120px;">Actividad</th>
                        <th style="width: 130px;">OT / Ticket</th>
                        <th style="width: 130px;">Clase</th>
                        <th style="width: 150px;">Serie</th>
                        <th>Observaciones (Bitácora de Acciones)</th>
                    </tr>
                </thead>
                <tbody id="body-actividades">
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #64748b; font-weight: 600;">
                            Seleccione un técnico y una fecha para cargar el reporte.
                        </td>
                    </tr>
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
    let actividadesRaw = [];

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('tecnico-filtro').addEventListener('change', cargarActividades);
        document.getElementById('fecha-filtro').addEventListener('change', cargarActividades);
        document.getElementById('btn-exportar').addEventListener('click', exportarExcel);
    });

    // Definición de las 9 horas de la jornada
    const horasJornada = [
        { key: 9,  label: "9:00 a  10:00" },
        { key: 10, label: "10:00 a 11:00" },
        { key: 11, label: "11:00 a 12:00" },
        { key: 12, label: "12:00 a 13:00" },
        { key: 13, label: "13:00 a 14:00" },
        { key: 14, label: "14:00 a 15:00", esAlmuerzo: true },
        { key: 15, label: "15:00 a 16:00" },
        { key: 16, label: "16:00 a 17:00" },
        { key: 17, label: "17:00 a 18:00" }
    ];

    function cargarActividades() {
        const tecnicoId = document.getElementById('tecnico-filtro').value;
        const fecha = document.getElementById('fecha-filtro').value;
        const container = document.getElementById('body-actividades');
        const btnExportar = document.getElementById('btn-exportar');

        if (!tecnicoId) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color: #64748b; font-weight: 600;">Seleccione un técnico y una fecha para cargar el reporte.</td></tr>';
            btnExportar.disabled = true;
            actualizarEstadisticasVacias();
            return;
        }

        container.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Cargando actividades...</td></tr>';
        btnExportar.disabled = true;

        fetch(`{{ route('actividades.admin.listar') }}?tecnico_id=${tecnicoId}&fecha=${fecha}`)
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    actividadesRaw = res.actividades;
                    renderizarTabla(fecha);
                    btnExportar.disabled = false;
                } else {
                    container.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${res.error}</td></tr>`;
                    actualizarEstadisticasVacias();
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al obtener actividades.</td></tr>';
                actualizarEstadisticasVacias();
            });
    }

    function parseHour(fechaHoraStr) {
        try {
            const timePart = fechaHoraStr.includes('T') ? fechaHoraStr.split('T')[1] : fechaHoraStr.split(' ')[1];
            return parseInt(timePart.split(':')[0]);
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

            let rowHtml = '';
            let valActividad = 'sn';
            let valNovedad = 'sn';
            let valEstado = 'sn';
            let ots = 'sn';
            let clase = 'sn';
            let serie = 'sn';
            let observaciones = 'sn';

            if (slot.esAlmuerzo) {
                valActividad = 'almuerzo';
                valNovedad = 'Oficina';
                valEstado = 'realizado ';
                observaciones = 'Almuerzo';
            }

            const manualAct = slotActs.find(a => a.tipo_accion === 'registro_manual');
            if (manualAct) {
                const meta = manualAct.metadata_json || {};
                valActividad = meta.actividad || 'sn';
                valNovedad = meta.novedad || 'sn';
                valEstado = meta.estado || 'sn';
                valModalidad = meta.modalidad || 'presencial';
                ots = meta.ot || 'sn';
                clase = meta.clase || 'sn';
                serie = meta.serie || 'sn';
                observaciones = manualAct.descripcion || 'sn';
                if (!slot.esAlmuerzo) {
                    horasTrabajadas++;
                }
                if (ots !== 'sn' && ots.trim() !== '') {
                    ots.split(',').map(o => o.trim()).forEach(o => otsSet.add(o));
                }
            } else if (slotActs.length > 0) {
                if (!slot.esAlmuerzo) {
                    horasTrabajadas++;
                }

                observaciones = slotActs.map(a => a.descripcion).join(' | ');
                if (slot.esAlmuerzo) {
                    observaciones = 'Almuerzo | ' + observaciones;
                }

                const otsEnSlot = slotActs.map(a => a.metadata_json?.nro_orden).filter(Boolean);
                if (otsEnSlot.length > 0) {
                    ots = [...new Set(otsEnSlot)].join(', ');
                    otsEnSlot.forEach(o => otsSet.add(o));
                }

                const mainAct = slotActs.find(a => a.metadata_json?.nro_orden);
                if (mainAct) {
                    clase = mapClase(mainAct.metadata_json?.tipo);
                    serie = mainAct.metadata_json?.serie || 'sn';
                    
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
            }

            const badgeEstado = valEstado === 'sn' ? 'sn' : (valEstado === 'realizado ' ? 'realizado' : (valEstado === 'pendiente' ? 'pendiente' : (valEstado === 'en proceso' ? 'proceso' : 'almuerzo')));

            rowHtml = `
                <tr>
                    <td style="font-weight: 700; color: #475569;">${slot.label}</td>
                    <td><span class="act-badge act-badge-${badgeEstado === 'almuerzo' && slot.esAlmuerzo ? 'almuerzo' : badgeEstado}">${valActividad}</span></td>
                    <td><span style="font-family: monospace; font-weight: 700; color: #2563eb;">${ots}</span></td>
                    <td>${clase}</td>
                    <td style="font-family: monospace;">${serie}</td>
                    <td style="font-weight: 500; font-size: 12.5px;">${observaciones}</td>
                </tr>
            `;
            container.innerHTML += rowHtml;
        });

        document.getElementById('stat-horas').textContent = `${horasTrabajadas}/8`;
        document.getElementById('stat-acciones').textContent = totalAcciones;
        document.getElementById('stat-ots').textContent = otsSet.size;
    }

    function actualizarEstadisticasVacias() {
        document.getElementById('stat-horas').textContent = '0/8';
        document.getElementById('stat-acciones').textContent = '0';
        document.getElementById('stat-ots').textContent = '0';
    }

    async function exportarExcel() {
        const selectTecnico = document.getElementById('tecnico-filtro');
        const tecnicoNombre = selectTecnico.options[selectTecnico.selectedIndex].text;
        
        if (!actividadesRaw.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Reporte sin datos',
                text: 'El técnico seleccionado no tiene actividades en este día. El excel se generará vacío.',
                confirmButtonColor: '#2563eb'
            });
        }

        const fecha = document.getElementById('fecha-filtro').value;
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

                if (slot.esAlmuerzo) {
                    valActividad = 'almuerzo';
                    valNovedad = 'Oficina';
                    valEstado = 'realizado ';
                    observaciones = 'Almuerzo';
                }

                const manualAct = slotActs.find(a => a.tipo_accion === 'registro_manual');
                if (manualAct) {
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
                } else if (slotActs.length > 0) {
                    observaciones = slotActs.map(a => a.descripcion).join(' | ');
                    if (slot.esAlmuerzo) {
                        observaciones = 'Almuerzo | ' + observaciones;
                    }

                    const otsEnSlot = slotActs.map(a => a.metadata_json?.nro_orden).filter(Boolean);
                    if (otsEnSlot.length > 0) {
                        ots = [...new Set(otsEnSlot)].join(', ');
                    }

                    const mainAct = slotActs.find(a => a.metadata_json?.nro_orden);
                    if (mainAct) {
                        clase = mapClase(mainAct.metadata_json?.tipo);
                        serie = mainAct.metadata_json?.serie || 'sn';
                        
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

            ws.addRow([]); // Fila 11
            const commitRow = ws.addRow([]); // Fila 12
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
                text: `El reporte del técnico ${tecnicoNombre} se ha generado correctamente.`,
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
</script>
@endpush
