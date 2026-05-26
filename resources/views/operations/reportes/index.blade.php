@extends('layouts.app')
@section('titulo', 'Reportes de Órdenes')

@push('css_adicional')
<style>
.rep-container { max-width: 1400px; margin: 0 auto; padding: 28px 24px; }
.rep-hdr { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.rep-hdr h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.rep-hdr p { margin: 0; color: #64748b; font-size: 14px; }
.rep-filtros { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end; }
.campo { display: flex; flex-direction: column; gap: 6px; }
.campo label { font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; }
.campo input, .campo select { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff; transition: border-color .2s; }
.campo input:focus, .campo select:focus { outline: none; border-color: #2563eb; }
.btn-filtrar { background: #2563eb; color: #fff; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background .2s; display: flex; align-items: center; justify-content: center; gap: 8px; height: 42px; }
.btn-filtrar:hover { background: #1d4ed8; }
.btn-exportar { background: #10b981; color: #fff; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background .2s; display: flex; align-items: center; justify-content: center; gap: 8px; height: 42px; }
.btn-exportar:hover { background: #059669; }
.rep-resultados { background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
.rep-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.rep-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
.rep-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
.rep-table tr:hover td { background: #f8fafc; }
.badge { font-family: monospace; font-size: 12px; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; }
.msg-info { padding: 30px; text-align: center; color: #64748b; font-size: 14px; }
.resumen-bar { padding: 12px 20px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #334155; display: flex; justify-content: space-between; }
</style>
@endpush

@section('contenido')
<div class="rep-container">
    <div class="rep-hdr">
        <div>
            <h2><i class="bi bi-bar-chart-fill"></i> Reportes Generales</h2>
            <p>Filtrado y exportación de datos operativos de órdenes técnicas.</p>
        </div>
        <button class="btn-exportar" onclick="exportarExcel('tabla-reporte', 'Reporte_Ordenes')">
            <i class="bi bi-file-earmark-excel"></i> Exportar CSV
        </button>
    </div>

    <div class="rep-filtros">
        <form id="form-filtros" class="filtros-grid" onsubmit="event.preventDefault(); ejecutarReporte();">
            <div class="campo">
                <label>Fecha Inicio</label>
                <input type="date" id="f_inicio" name="fecha_inicio">
            </div>
            <div class="campo">
                <label>Fecha Fin</label>
                <input type="date" id="f_fin" name="fecha_fin">
            </div>
            <div class="campo">
                <label>Estado</label>
                <select id="f_estado" name="estado">
                    <option value="">TODOS LOS ESTADOS</option>
                    @foreach($estados as $est)
                        <option value="{{ $est }}">{{ $est }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label>Técnico</label>
                <select id="f_tecnico" name="tecnico_id">
                    <option value="">TODOS LOS TÉCNICOS</option>
                    @foreach($tecnicos as $tec)
                        <option value="{{ $tec->id }}">{{ $tec->nombre_tecnico }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label>Sucursal</label>
                <select id="f_sucursal" name="sucursal_id">
                    <option value="">TODAS LAS SUCURSALES</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->ciudad }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-filtrar" id="btn-buscar">
                <i class="bi bi-search"></i> Filtrar
            </button>
        </form>
    </div>

    <div class="rep-resultados">
        <div class="resumen-bar" id="resumen-bar" style="display:none;">
            <span>Total de registros encontrados: <strong id="total-registros">0</strong></span>
        </div>
        <div style="overflow-x:auto; max-height: 600px; overflow-y: auto;">
            <table class="rep-table" id="tabla-reporte">
                <thead>
                    <tr>
                        <th>Nro. Orden</th>
                        <th>Fecha Ingreso</th>
                        <th>Cliente</th>
                        <th>Identificación</th>
                        <th>Equipo</th>
                        <th>Serie</th>
                        <th>Técnico</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbody-resultados">
                    <tr><td colspan="9"><div class="msg-info">Utilice los filtros para generar el reporte.</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
async function ejecutarReporte() {
    const btn = document.getElementById('btn-buscar');
    const tbody = document.getElementById('tbody-resultados');
    const frm = document.getElementById('form-filtros');
    
    const formData = new FormData(frm);
    const params = new URLSearchParams(formData).toString();

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass"></i>...';
    tbody.innerHTML = '<tr><td colspan="9"><div class="msg-info">Cargando datos...</div></td></tr>';
    document.getElementById('resumen-bar').style.display = 'none';

    try {
        const response = await fetch(`{{ route('reportes.filtrar') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await response.json();

        if (d.ok) {
            renderizarTabla(d.data);
        } else {
            tbody.innerHTML = `<tr><td colspan="9"><div class="msg-info" style="color:#ef4444;">${d.error}</div></td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="msg-info" style="color:#ef4444;">Error de comunicación con el servidor.</div></td></tr>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search"></i> Filtrar';
    }
}

function renderizarTabla(datos) {
    const tbody = document.getElementById('tbody-resultados');
    document.getElementById('total-registros').textContent = datos.length;
    document.getElementById('resumen-bar').style.display = 'flex';

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="msg-info">No se encontraron registros con los criterios seleccionados.</div></td></tr>';
        return;
    }

    let html = '';
    datos.forEach(ord => {
        // Formateo simple de fecha
        const f = new Date(ord.fecha_de_ingreso);
        const fechaFmt = f.toLocaleDateString() + ' ' + f.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        html += `
            <tr>
                <td><span class="badge">${ord.nro_orden}</span></td>
                <td>${fechaFmt}</td>
                <td>${ord.cliente ? ord.cliente.nombres + ' ' + ord.cliente.apellidos : '-'}</td>
                <td>${ord.cliente ? ord.cliente.identificacion : '-'}</td>
                <td>${ord.equipo ? ord.equipo.marca + ' ' + ord.equipo.modelo : '-'}</td>
                <td>${ord.equipo ? ord.equipo.serie : '-'}</td>
                <td>${ord.tecnico ? ord.tecnico.nombre_tecnico : '-'}</td>
                <td>${ord.sucursal ? ord.sucursal.ciudad : '-'}</td>
                <td style="font-weight:700;">${ord.estado_orden}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function exportarExcel(tableID, filename = '') {
    let csv = [];
    let rows = document.querySelectorAll("#" + tableID + " tr");
    
    // Validar si la tabla tiene el mensaje de "sin registros" o esta cargando
    if(rows.length <= 2 && rows[1] && rows[1].querySelector('.msg-info')) {
        alert("No hay datos para exportar.");
        return;
    }

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }

    let csvFile = new Blob([csv.join("\n")], {type: "text/csv;charset=utf-8;"});
    let downloadLink = document.createElement("a");
    downloadLink.download = filename + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endpush