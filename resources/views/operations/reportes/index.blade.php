@extends('layouts.app')
@section('titulo', 'Reportes')

@push('css_adicional')
<style>
.rep-wrap{max-width:1380px;margin:0 auto;padding:28px 20px}
.rep-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.rep-head h2{margin:0;font-size:22px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px}
.rep-head p{margin:6px 0 0;color:#64748b;font-size:13px}
.rep-card{background:#fff;border-radius:14px;border:1px solid #e8edf3;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:16px;overflow:hidden}
.rep-card-head{padding:12px 18px;background:#f8fbff;border-bottom:1px solid #dbe6fb;color:#1e40af;font-size:12px;font-weight:700;text-transform:uppercase}
.rep-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;padding:14px 18px}
.rep-campo{display:flex;flex-direction:column;gap:4px}
.rep-campo label{font-size:11px;font-weight:700;color:#475569;text-transform:uppercase}
.rep-campo input,.rep-campo select{border:1.5px solid #dbe2ea;border-radius:8px;padding:8px 10px;font-size:13px}
.rep-actions{display:flex;gap:8px;flex-wrap:wrap;padding:0 18px 14px}
.rep-btn{border:none;border-radius:8px;padding:9px 14px;font-weight:700;font-size:12px;cursor:pointer}
.rep-btn.primary{background:#2563eb;color:#fff}
.rep-btn.dark{background:#0f172a;color:#fff}
.rep-btn.green{background:#059669;color:#fff}
.rep-btn.ghost{background:#f1f5f9;color:#334155}
.rep-kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:14px}
.kpi{background:#fff;border:1px solid #e8edf3;border-radius:10px;padding:12px;text-align:center}
.kpi .val{font-size:24px;font-weight:800;color:#0f172a;line-height:1}
.kpi .lbl{font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:4px}
.rep-charts{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.rep-charts-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px}
.rep-chart{background:#fff;border:1px solid #e8edf3;border-radius:12px}
.rep-chart h4{margin:0;padding:10px 14px 0;font-size:12px;color:#1e40af}
.rep-chart .body{padding:10px 14px 14px;height:220px}
.rep-table-wrap{overflow:auto;max-height:560px}
.rep-table{width:100%;border-collapse:collapse;font-size:12px}
.rep-table th{position:sticky;top:0;background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:9px 10px;text-align:left;font-size:10px;color:#64748b;text-transform:uppercase}
.rep-table td{padding:8px 10px;border-bottom:1px solid #f1f5f9;color:#1e293b;white-space:nowrap}
.rep-nro{font-family:monospace;font-weight:800;color:#2563eb}
.rep-empty{padding:34px;text-align:center;color:#94a3b8}
.rep-top{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid #eef2f7}
.rep-top input{border:1.5px solid #dbe2ea;border-radius:8px;padding:7px 10px;font-size:12px;min-width:220px}
@media(max-width:1100px){.rep-kpis{grid-template-columns:repeat(3,1fr)}}
@media(max-width:1100px){.rep-charts-3{grid-template-columns:1fr 1fr}}
@media(max-width:900px){.rep-charts,.rep-charts-3{grid-template-columns:1fr}.rep-kpis{grid-template-columns:repeat(2,1fr)}}
</style>
@endpush

@section('contenido')
<div class="rep-wrap">
    <div class="rep-head">
        <div>
            <h2><i class="bi bi-bar-chart-line-fill" style="color:#2563eb;"></i> Reportes</h2>
            <p>Filtros avanzados, KPIs operativos, gráficas y exportes.</p>
        </div>
    </div>

    <div class="rep-card">
        <div class="rep-card-head">Filtros</div>
        <form id="rep-form">
            <div class="rep-grid">
                <div class="rep-campo"><label>Técnico</label>
                    <select name="tecnico_id"><option value="">Todos</option>@foreach($tecnicos as $t)<option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>@endforeach</select>
                </div>
                @if($esMaster)
                <div class="rep-campo"><label>Sucursal</label>
                    <select name="sucursal_id"><option value="">Todas</option>@foreach($sucursales as $s)<option value="{{ $s->id }}">{{ $s->ciudad }}</option>@endforeach</select>
                </div>
                @endif
                <div class="rep-campo"><label>Estado orden</label>
                    <select name="estado"><option value="">Todos</option>@foreach($estados as $e)<option value="{{ $e }}">{{ $e }}</option>@endforeach</select>
                </div>
                <div class="rep-campo"><label>Tipo orden</label>
                    <select name="tipo_orden"><option value="">Todos</option><option value="personal">Personal</option><option value="empresa">Empresa</option></select>
                </div>
                <div class="rep-campo"><label>Marca</label>
                    <select name="marca"><option value="">Todas</option>@foreach($marcas as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
                </div>
                <div class="rep-campo"><label>Tipo equipo</label>
                    <select name="tipo_equipo"><option value="">Todos</option>@foreach($tiposEquipo as $tp)<option value="{{ $tp }}">{{ $tp }}</option>@endforeach</select>
                </div>
                <div class="rep-campo"><label>Motivo ingreso</label>
                    <select name="motivo_ingreso"><option value="">Todos</option>@foreach($motivos as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
                </div>
                <div class="rep-campo"><label>Estado repuesto</label>
                    <select name="estado_repuesto"><option value="">Todos</option>@foreach($estadosRepuesto as $er)<option value="{{ $er }}">{{ $er }}</option>@endforeach<option value="No requerido">No requerido</option></select>
                </div>
                <div class="rep-campo"><label>Estado garantía</label>
                    <select name="estado_garantia"><option value="">Todos</option>@foreach($estadosGarantia as $eg)<option value="{{ $eg }}">{{ $eg }}</option>@endforeach</select>
                </div>
                <div class="rep-campo"><label>Fecha desde</label><input type="date" name="fecha_inicio"></div>
                <div class="rep-campo"><label>Fecha hasta</label><input type="date" name="fecha_fin"></div>
            </div>
            <div class="rep-actions">
                <button type="submit" class="rep-btn primary" id="btn-filtrar"><i class="bi bi-search"></i> Generar</button>
                <button type="button" class="rep-btn ghost" id="btn-limpiar"><i class="bi bi-x-circle"></i> Limpiar</button>
                <button type="button" class="rep-btn dark" id="btn-pdf"><i class="bi bi-printer"></i> PDF</button>
                <button type="button" class="rep-btn green" id="btn-csv"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</button>
                <button type="button" class="rep-btn green" id="btn-xlsx"><i class="bi bi-file-earmark-excel"></i> XLSX</button>
            </div>
        </form>
    </div>

    <div id="rep-resultados" style="display:none;">
        <div class="rep-kpis">
            <div class="kpi"><div class="val" id="k-total">0</div><div class="lbl">Total</div></div>
            <div class="kpi"><div class="val" id="k-p">0</div><div class="lbl">Pendientes</div></div>
            <div class="kpi"><div class="val" id="k-ep">0</div><div class="lbl">En proceso</div></div>
            <div class="kpi"><div class="val" id="k-f">0</div><div class="lbl">Finalizadas</div></div>
            <div class="kpi"><div class="val" id="k-e">0</div><div class="lbl">Entregadas</div></div>
            <div class="kpi"><div class="val" id="k-nc">0</div><div class="lbl">Nota crédito</div></div>
        </div>

        <div class="rep-charts">
            <div class="rep-chart"><h4>Distribución por estado</h4><div class="body"><canvas id="ch-estados"></canvas></div></div>
            <div class="rep-chart"><h4>Órdenes por técnico</h4><div class="body"><canvas id="ch-tecnicos"></canvas></div></div>
        </div>
        <div class="rep-charts-3">
            <div class="rep-chart"><h4>Top marcas</h4><div class="body"><canvas id="ch-marcas"></canvas></div></div>
            <div class="rep-chart"><h4>Tipo de equipo</h4><div class="body"><canvas id="ch-tipos"></canvas></div></div>
            <div class="rep-chart"><h4>Personal vs Empresa</h4><div class="body"><canvas id="ch-tipoorden"></canvas></div></div>
        </div>

        <div class="rep-card">
            <div class="rep-top">
                <strong id="rep-total-txt">0 órdenes</strong>
                <input id="rep-buscar" type="text" placeholder="Buscar en la tabla...">
            </div>
            <div class="rep-table-wrap" id="rep-print-area">
                <table class="rep-table" id="rep-tabla">
                    <thead>
                    <tr>
                        <th>Nro</th><th>Fecha</th><th>Tipo</th><th>Cliente</th><th>Identificación</th><th>Equipo</th>
                        <th>Serie</th><th>Marca</th><th>Tipo Eq.</th><th>Motivo</th><th>Estado repuesto</th>
                        <th>Estado garantía</th><th>Técnico</th><th>Sucursal</th><th>Estado</th>
                    </tr>
                    </thead>
                    <tbody id="rep-body"><tr><td colspan="15" class="rep-empty">Use los filtros para generar el reporte.</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
let _rows = [];
let _filtered = [];
let _charts = {};

const form = document.getElementById('rep-form');
const repResultados = document.getElementById('rep-resultados');
const repBody = document.getElementById('rep-body');
const btnFiltrar = document.getElementById('btn-filtrar');

function esc(v){ return String(v ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function countBy(arr, key){ const out={}; arr.forEach(r=>{ const v=(r[key]||'Sin dato'); out[v]=(out[v]||0)+1; }); return out; }

function normalizeRow(raw){
    const cliente = raw.cliente_nombre || ((raw.cliente?.nombres || '') + ' ' + (raw.cliente?.apellidos || '')).trim();
    const identificacion = raw.identificacion || raw.cliente?.identificacion || '';
    return {
        nro_orden: raw.nro_orden || '',
        fecha_de_ingreso: raw.fecha_de_ingreso || '',
        tipo_orden: raw.tipo_orden || '',
        cliente_nombre: cliente || '-',
        identificacion: identificacion || '-',
        equipo_nombre: raw.equipo_nombre || [raw.equipo?.marca, raw.equipo?.modelo].filter(Boolean).join(' ') || '-',
        serie: raw.serie || raw.equipo?.serie || '-',
        marca: raw.marca || raw.equipo?.marca || '-',
        tipo_equipo: raw.tipo_equipo || raw.equipo?.tipo || '-',
        motivo_ingreso: raw.motivo_ingreso || '-',
        estado_repuesto: raw.estado_repuesto || '-',
        estado_garantia: raw.estado_garantia || '-',
        tecnico_nombre: raw.tecnico_nombre || raw.tecnico?.nombre_tecnico || '-',
        sucursal_nombre: raw.sucursal_nombre || raw.sucursal?.ciudad || '-',
        estado_orden: raw.estado_orden || '-',
    };
}

function renderKpis(rows){
    const c = countBy(rows, 'estado_orden');
    document.getElementById('k-total').textContent = rows.length;
    document.getElementById('k-p').textContent = c['Pendiente'] || c['PENDIENTE'] || 0;
    document.getElementById('k-ep').textContent = c['En proceso'] || c['EN PROCESO'] || 0;
    document.getElementById('k-f').textContent = c['Finalizada'] || c['FINALIZADA'] || 0;
    document.getElementById('k-e').textContent = c['Entregada'] || c['ENTREGADA'] || 0;
    document.getElementById('k-nc').textContent = c['Nota de Credito'] || c['Nota de Crédito'] || c['NOTA DE CREDITO'] || 0;
}

function destroyCharts(){
    Object.keys(_charts).forEach(k => _charts[k].destroy());
    _charts = {};
}

function renderCharts(rows){
    destroyCharts();
    const estados = countBy(rows, 'estado_orden');
    const tecnicos = countBy(rows, 'tecnico_nombre');
    const marcas = countBy(rows, 'marca');
    const tipos = countBy(rows, 'tipo_equipo');
    const tipoOrden = countBy(rows, 'tipo_orden');
    _charts.estados = new Chart(document.getElementById('ch-estados'), {
        type: 'doughnut',
        data: { labels: Object.keys(estados), datasets:[{ data: Object.values(estados) }] },
        options: { maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
    });
    _charts.tecnicos = new Chart(document.getElementById('ch-tecnicos'), {
        type: 'bar',
        data: { labels: Object.keys(tecnicos), datasets:[{ data: Object.values(tecnicos), backgroundColor:'#2563eb' }] },
        options: { maintainAspectRatio:false, plugins:{ legend:{ display:false } } }
    });
    _charts.marcas = new Chart(document.getElementById('ch-marcas'), {
        type: 'bar',
        data: { labels: Object.keys(marcas).slice(0, 8), datasets:[{ data: Object.values(marcas).slice(0, 8), backgroundColor:'#f59e0b' }] },
        options: { maintainAspectRatio:false, plugins:{ legend:{ display:false } } }
    });
    _charts.tipos = new Chart(document.getElementById('ch-tipos'), {
        type: 'pie',
        data: { labels: Object.keys(tipos), datasets:[{ data: Object.values(tipos) }] },
        options: { maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
    });
    _charts.tipoorden = new Chart(document.getElementById('ch-tipoorden'), {
        type: 'doughnut',
        data: { labels: Object.keys(tipoOrden), datasets:[{ data: Object.values(tipoOrden) }] },
        options: { maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
    });
}

function renderTable(rows){
    document.getElementById('rep-total-txt').textContent = `${rows.length} órdenes`;
    if(!rows.length){
        repBody.innerHTML = '<tr><td colspan="15" class="rep-empty">No se encontraron registros.</td></tr>';
        return;
    }
    repBody.innerHTML = rows.map(r => `
        <tr>
            <td class="rep-nro">${esc(r.nro_orden)}</td><td>${esc(r.fecha_de_ingreso)}</td><td>${esc(r.tipo_orden)}</td>
            <td>${esc(r.cliente_nombre)}</td><td>${esc(r.identificacion)}</td><td>${esc(r.equipo_nombre)}</td>
            <td>${esc(r.serie)}</td><td>${esc(r.marca)}</td><td>${esc(r.tipo_equipo)}</td><td>${esc(r.motivo_ingreso)}</td>
            <td>${esc(r.estado_repuesto)}</td><td>${esc(r.estado_garantia)}</td><td>${esc(r.tecnico_nombre)}</td>
            <td>${esc(r.sucursal_nombre)}</td><td><strong>${esc(r.estado_orden)}</strong></td>
        </tr>
    `).join('');
}

async function cargarReporte(){
    const params = new URLSearchParams(new FormData(form));
    btnFiltrar.disabled = true;
    btnFiltrar.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    try{
        const r = await fetch(`{{ route('reportes.filtrar') }}?${params.toString()}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
        const d = await r.json();
        if(!d.ok){ throw new Error(d.error || 'No se pudo procesar el reporte.'); }
        _rows = (d.data || []).map(normalizeRow);
        _filtered = _rows.slice();
        repResultados.style.display = 'block';
        renderKpis(_rows);
        renderCharts(_rows);
        renderTable(_filtered);
    }catch(e){
        repResultados.style.display = 'block';
        repBody.innerHTML = `<tr><td colspan="15" class="rep-empty" style="color:#dc2626;">${esc(e.message)}</td></tr>`;
        destroyCharts();
    }finally{
        btnFiltrar.disabled = false;
        btnFiltrar.innerHTML = '<i class="bi bi-search"></i> Generar';
    }
}

function exportCSV(){
    if(!_filtered.length){ alert('No hay datos para exportar.'); return; }
    const headers = ['Nro','Fecha','Tipo','Cliente','Identificación','Equipo','Serie','Marca','Tipo Eq.','Motivo','Estado repuesto','Estado garantía','Técnico','Sucursal','Estado'];
    const lines = [headers.join(',')];
    _filtered.forEach(r => {
        lines.push([
            r.nro_orden,r.fecha_de_ingreso,r.tipo_orden,r.cliente_nombre,r.identificacion,r.equipo_nombre,r.serie,r.marca,r.tipo_equipo,
            r.motivo_ingreso,r.estado_repuesto,r.estado_garantia,r.tecnico_nombre,r.sucursal_nombre,r.estado_orden
        ].map(v => `"${String(v ?? '').replace(/"/g,'""')}"`).join(','));
    });
    const blob = new Blob([lines.join('\n')], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = 'reporte_ordenes.csv'; a.click();
}

function loadExcelJs(){
    return new Promise((resolve, reject) => {
        if(window.ExcelJS){ resolve(); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js';
        s.onload = resolve; s.onerror = reject;
        document.head.appendChild(s);
    });
}

async function exportXLSX(){
    if(!_filtered.length){ alert('No hay datos para exportar.'); return; }
    try{
        await loadExcelJs();
        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('Reporte');
        ws.addRow(['Nro','Fecha','Tipo','Cliente','Identificación','Equipo','Serie','Marca','Tipo Eq.','Motivo','Estado repuesto','Estado garantía','Técnico','Sucursal','Estado']);
        _filtered.forEach(r => ws.addRow([
            r.nro_orden,r.fecha_de_ingreso,r.tipo_orden,r.cliente_nombre,r.identificacion,r.equipo_nombre,r.serie,r.marca,r.tipo_equipo,
            r.motivo_ingreso,r.estado_repuesto,r.estado_garantia,r.tecnico_nombre,r.sucursal_nombre,r.estado_orden
        ]));
        ws.getRow(1).font = {bold:true};
        ws.columns.forEach(c => c.width = 20);
        const buf = await wb.xlsx.writeBuffer();
        const blob = new Blob([buf], {type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob); a.download = 'reporte_ordenes.xlsx'; a.click();
    }catch{
        alert('No se pudo exportar a XLSX.');
    }
}

form.addEventListener('submit', function(e){ e.preventDefault(); cargarReporte(); });
document.getElementById('btn-limpiar').addEventListener('click', function(){
    form.reset();
    _rows = []; _filtered = [];
    repResultados.style.display = 'none';
    destroyCharts();
});
document.getElementById('rep-buscar').addEventListener('input', function(){
    const term = this.value.trim().toLowerCase();
    _filtered = !term ? _rows.slice() : _rows.filter(r => Object.values(r).join(' ').toLowerCase().includes(term));
    renderTable(_filtered);
});
document.getElementById('btn-csv').addEventListener('click', exportCSV);
document.getElementById('btn-xlsx').addEventListener('click', exportXLSX);
document.getElementById('btn-pdf').addEventListener('click', () => window.print());
</script>
@endpush
