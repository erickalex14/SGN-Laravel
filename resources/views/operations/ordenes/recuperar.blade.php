@extends('layouts.app')
@section('titulo', 'Recuperación de Órdenes')

@push('css_adicional')
<style>
:root {
    --rec-blue: #1e40af;
    --rec-blue-light: #eff6ff;
    --rec-border: #e2e8f0;
    --rec-slate: #0f172a;
    --rec-muted: #64748b;
    --rec-success: #10b981;
}

.rec-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.rec-header {
    margin-bottom: 25px;
}
.rec-header h2 {
    font-size: 22px;
    font-weight: 800;
    color: var(--rec-slate);
    margin: 0 0 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.rec-header p {
    color: var(--rec-muted);
    font-size: 13.5px;
    margin: 0;
}

/* Wizard Steps Indicator */
.rec-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}
.rec-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 3px;
    background: var(--rec-border);
    z-index: 1;
}
.rec-step-node {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 30%;
}
.rec-step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid var(--rec-border);
    color: var(--rec-muted);
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .25s ease;
}
.rec-step-node.activo .rec-step-circle {
    border-color: var(--rec-blue);
    background: var(--rec-blue);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(30,64,175,.15);
}
.rec-step-node.completado .rec-step-circle {
    border-color: var(--rec-success);
    background: var(--rec-success);
    color: #fff;
}
.rec-step-lbl {
    margin-top: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--rec-muted);
    text-transform: uppercase;
    letter-spacing: .03em;
}
.rec-step-node.activo .rec-step-lbl {
    color: var(--rec-blue);
}
.rec-step-node.completado .rec-step-lbl {
    color: var(--rec-success);
}

/* Card panels */
.rec-panel {
    background: #fff;
    border: 1.5px solid var(--rec-border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 14px rgba(0,0,0,.04);
}

/* Drag and Drop Zone */
.rec-dropzone {
    border: 2.5px dashed #cbd5e1;
    border-radius: 14px;
    background: var(--rec-blue-light);
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
}
.rec-dropzone:hover {
    border-color: var(--rec-blue);
    background: #e0f2fe;
}
.rec-dropzone i {
    font-size: 42px;
    color: var(--rec-blue);
    margin-bottom: 15px;
    display: block;
}
.rec-dropzone h4 {
    font-size: 15px;
    font-weight: 800;
    color: var(--rec-slate);
    margin-bottom: 5px;
}
.rec-dropzone p {
    font-size: 12.5px;
    color: var(--rec-muted);
    margin-bottom: 15px;
}

/* Loading panel */
.rec-loading {
    display: none;
    text-align: center;
    padding: 30px;
}
.rec-loading .spinner {
    font-size: 32px;
    color: var(--rec-blue);
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-bottom: 12px;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Form layout */
.rec-form-section {
    margin-bottom: 22px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--rec-border);
}
.rec-form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
.rec-section-h {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--rec-slate);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rec-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 18px;
}
.rec-col-full {
    grid-column: 1 / -1;
}
.rec-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.rec-field label {
    font-size: 11px;
    font-weight: 700;
    color: var(--rec-muted);
    text-transform: uppercase;
    letter-spacing: .02em;
}
.rec-input, .rec-select, .rec-textarea {
    border: 1.5px solid var(--rec-border);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    color: var(--rec-slate);
    background: #f8fafc;
    font-family: inherit;
    outline: none;
    transition: all .15s ease;
}
.rec-input:focus, .rec-select:focus, .rec-textarea:focus {
    border-color: var(--rec-blue);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(30,64,175,.08);
}
.rec-textarea {
    resize: vertical;
}

/* Badge alert */
.rec-alert {
    background: #fffbeb;
    border: 1px solid #fef08a;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 12.5px;
    color: #854d0e;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Actions */
.rec-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.btn-rec {
    border: none;
    border-radius: 9px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all .15s ease;
}
.btn-rec-primary {
    background: linear-gradient(135deg, var(--rec-blue), #1d4ed8);
    color: #fff;
    box-shadow: 0 4px 10px rgba(30,64,175,.2);
}
.btn-rec-primary:hover {
    opacity: .9;
    transform: translateY(-1px);
}
.btn-rec-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid var(--rec-border);
}
.btn-rec-secondary:hover {
    background: #e2e8f0;
}

@media(max-width:768px) {
    .rec-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('contenido')
<div class="rec-container">
    <div class="rec-header">
        @if ($type === 'informe')
            <h2><i class="bi bi-file-earmark-medical" style="color:var(--rec-blue);"></i>Zona de Recuperación de Informes Técnicos</h2>
            <p>Sube la copia en PDF de un informe técnico para reconstruirlo y asociarlo a la orden en el sistema.</p>
        @else
            <h2><i class="bi bi-file-earmark-plus" style="color:var(--rec-blue);"></i>Zona de Recuperación de Órdenes de Trabajo (OT)</h2>
            <p>Sube la copia en PDF de una orden de trabajo (OT) para reconstruirla automáticamente en el sistema.</p>
        @endif
    </div>

    <!-- Steps Indicator -->
    <div class="rec-steps">
        <div class="rec-step-node activo" id="step-node-1">
            <span class="rec-step-circle">1</span>
            <span class="rec-step-lbl">Cargar PDF</span>
        </div>
        <div class="rec-step-node" id="step-node-2">
            <span class="rec-step-circle">2</span>
            <span class="rec-step-lbl">Revisar Datos</span>
        </div>
        <div class="rec-step-node" id="step-node-3">
            <span class="rec-step-circle">3</span>
            <span class="rec-step-lbl">Completado</span>
        </div>
    </div>

    <div class="rec-panel">
        <!-- Step 1: Upload -->
        <div id="rec-step-1-content">
            <form id="upload-form" enctype="multipart/form-data">
                @csrf
                <div class="rec-dropzone" onclick="document.getElementById('pdf-file-input').click()">
                    <i class="bi bi-file-earmark-pdf"></i>
                    @if ($type === 'informe')
                        <h4>Arrastra o selecciona el PDF del informe técnico</h4>
                    @else
                        <h4>Arrastra o selecciona el PDF de la orden de trabajo (OT)</h4>
                    @endif
                    <p>Formatos permitidos: PDF de hasta 10MB</p>
                    <input type="file" id="pdf-file-input" name="pdf_file" accept=".pdf" style="display:none;" onchange="onFileSelected(this)">
                    <button type="button" class="btn-rec btn-rec-primary" style="margin: 0 auto;">
                        <i class="bi bi-folder-open"></i> Explorar Archivos
                    </button>
                </div>
            </form>

            <div class="rec-loading" id="analyzing-loader">
                <span class="spinner"><i class="bi bi-arrow-repeat"></i></span>
                <h4>Analizando documento...</h4>
                <p>Extrayendo texto y mapeando campos automáticamente. Por favor espera.</p>
            </div>
        </div>

        <!-- Step 2: Form review -->
        <div id="rec-step-2-content" style="display:none;">
            <div id="rec-warning-msg" class="rec-alert" style="display:none;"></div>

            <form id="recovery-form">
                @csrf
                <input type="hidden" name="is_informe" id="field-is-informe" value="{{ $type === 'informe' ? '1' : '0' }}">

                <!-- 1. Datos Generales -->
                <div class="rec-form-section">
                    <div class="rec-section-h"><i class="bi bi-file-text"></i> Datos del Registro</div>
                    <div class="rec-grid">
                        <div class="rec-field">
                            <label>Número de Orden <span class="text-danger">*</span></label>
                            <input type="text" name="nro_orden" id="field-nro-orden" class="rec-input" required placeholder="Ej: GYE-000123">
                        </div>
                        @if ($type !== 'informe')
                            <div class="rec-field">
                                <label>Número de Factura / Ticket</label>
                                <input type="text" name="nro_factura" id="field-nro-factura" class="rec-input" placeholder="Ej: 001-001-000000123">
                            </div>
                            <div class="rec-field">
                                <label>Sucursal Física <span class="text-danger">*</span></label>
                                <select name="sucursal_id" id="field-sucursal" class="rec-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->ciudad ?? $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="nro_factura" id="field-nro-factura" value="">
                            <input type="hidden" name="sucursal_id" id="field-sucursal" value="">
                        @endif
                        <div class="rec-field">
                            <label>Técnico Responsable <span class="text-danger">*</span></label>
                            <select name="tecnico_id" id="field-tecnico" class="rec-select" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($tecnicos as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre_tecnico }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($type !== 'informe')
                            <div class="rec-field rec-col-full">
                                <label>Motivo de Ingreso</label>
                                <input type="text" name="motivo_ingreso" id="field-motivo-ingreso" class="rec-input" placeholder="Ej: Servicio Cliente Externo">
                            </div>
                        @else
                            <input type="hidden" name="motivo_ingreso" id="field-motivo-ingreso" value="">
                        @endif
                    </div>
                </div>

                @if ($type !== 'informe')
                    <!-- 2. Datos del Cliente -->
                    <div class="rec-form-section">
                        <div class="rec-section-h"><i class="bi bi-person"></i> Datos del Cliente</div>
                        <div class="rec-grid">
                            <div class="rec-field">
                                <label>Cédula / RUC / Pasaporte <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_identificacion" id="field-cliente-identificacion" class="rec-input" required>
                            </div>
                            <div class="rec-field">
                                <label>Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_nombres" id="field-cliente-nombres" class="rec-input" required>
                            </div>
                            <div class="rec-field">
                                <label>Apellidos</label>
                                <input type="text" name="cliente_apellidos" id="field-cliente-apellidos" class="rec-input">
                            </div>
                            <div class="rec-field">
                                <label>Teléfono</label>
                                <input type="text" name="cliente_telefono" id="field-cliente-telefono" class="rec-input">
                            </div>
                            <div class="rec-field">
                                <label>Correo Electrónico</label>
                                <input type="email" name="cliente_correo" id="field-cliente-correo" class="rec-input">
                            </div>
                            <div class="rec-field">
                                <label>Dirección</label>
                                <input type="text" name="cliente_direccion" id="field-cliente-direccion" class="rec-input">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Datos del Equipo -->
                    <div class="rec-form-section">
                        <div class="rec-section-h"><i class="bi bi-cpu"></i> Datos del Equipo</div>
                        <div class="rec-grid">
                            <div class="rec-field">
                                <label>Tipo de Dispositivo <span class="text-danger">*</span></label>
                                <input type="text" name="equipo_tipo" id="field-equipo-tipo" class="rec-input" required placeholder="Ej: Laptop, Consola">
                            </div>
                            <div class="rec-field">
                                <label>Marca <span class="text-danger">*</span></label>
                                <input type="text" name="equipo_marca" id="field-equipo-marca" class="rec-input" required placeholder="Ej: ASUS, SONY">
                            </div>
                            <div class="rec-field">
                                <label>Código / Modelo <span class="text-danger">*</span></label>
                                <input type="text" name="equipo_modelo" id="field-equipo-modelo" class="rec-input" required placeholder="Ej: PlayStation 5">
                            </div>
                            <div class="rec-field">
                                <label>Serie / Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="equipo_serie" id="field-equipo-serie" class="rec-input" required placeholder="Ej: SN-987654">
                            </div>
                            <div class="rec-field rec-col-full">
                                <label>Falla Reportada</label>
                                <input type="text" name="falla" id="field-falla" class="rec-input">
                            </div>
                            <div class="rec-field rec-col-full">
                                <label>Observaciones</label>
                                <input type="text" name="observacion" id="field-observacion" class="rec-input">
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Elementos ocultos para evitar errores en JS populateForm --}}
                    <input type="hidden" name="cliente_identificacion" id="field-cliente-identificacion" value="">
                    <input type="hidden" name="cliente_nombres" id="field-cliente-nombres" value="">
                    <input type="hidden" name="cliente_apellidos" id="field-cliente-apellidos" value="">
                    <input type="hidden" name="cliente_telefono" id="field-cliente-telefono" value="">
                    <input type="hidden" name="cliente_correo" id="field-cliente-correo" value="">
                    <input type="hidden" name="cliente_direccion" id="field-cliente-direccion" value="">

                    <input type="hidden" name="equipo_tipo" id="field-equipo-tipo" value="">
                    <input type="hidden" name="equipo_marca" id="field-equipo-marca" value="">
                    <input type="hidden" name="equipo_modelo" id="field-equipo-modelo" value="">
                    <input type="hidden" name="equipo_serie" id="field-equipo-serie" value="">
                    <input type="hidden" name="falla" id="field-falla" value="">
                    <input type="hidden" name="observacion" id="field-observacion" value="">
                @endif

                <!-- 4. Campos del Informe Técnico (Solo si es tipo informe) -->
                <div class="rec-form-section" id="section-informe" style="display:none;">
                    <div class="rec-section-h"><i class="bi bi-file-earmark-medical"></i> Detalle del Informe Técnico</div>
                    <div class="rec-grid">
                        <div class="rec-field">
                            <label>Estado Final del Equipo</label>
                            <select name="estado_equipo" id="field-estado-equipo" class="rec-select">
                                <option value="Operativo">Operativo</option>
                                <option value="Reparado parcialmente">Reparado parcialmente</option>
                                <option value="Sin reparación posible">Sin reparación posible</option>
                                <option value="Desguace">Desguace</option>
                                <option value="En espera de repuesto">En espera de repuesto</option>
                            </select>
                        </div>
                        <div class="rec-field rec-col-full">
                            <label>Antecedentes</label>
                            <textarea name="antecedentes" id="field-antecedentes" rows="3" class="rec-textarea"></textarea>
                        </div>
                        <div class="rec-field rec-col-full">
                            <label>Proceso Técnico Realizado</label>
                            <textarea name="proceso" id="field-proceso" rows="3" class="rec-textarea"></textarea>
                        </div>
                        <div class="rec-field rec-col-full">
                            <label>Conclusión</label>
                            <textarea name="conclusion" id="field-conclusion" rows="3" class="rec-textarea"></textarea>
                        </div>
                        <div class="rec-field rec-col-full">
                            <label>Recomendaciones</label>
                            <textarea name="recomendaciones" id="field-recomendaciones" rows="3" class="rec-textarea"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rec-actions">
                    <button type="button" class="btn-rec btn-rec-secondary" onclick="restartWizard()">
                        <i class="bi bi-arrow-left"></i> Volver a Subir
                    </button>
                    <button type="button" id="btn-save-recovery" class="btn-rec btn-rec-primary" onclick="submitRecovery()">
                        <i class="bi bi-save"></i> Reconstruir Registro
                    </button>
                </div>
            </form>
        </div>

        <!-- Step 3: Success Confirmation -->
        <div id="rec-step-3-content" style="display:none; text-align:center; padding:30px 10px;">
            <i class="bi bi-check-circle-fill" style="font-size:56px; color:var(--rec-success); margin-bottom:15px; display:block;"></i>
            <h3 style="font-weight:800; color:var(--rec-slate); margin-bottom:8px;" id="success-title">¡Registro Reconstruido con Éxito!</h3>
            <p style="color:var(--rec-muted); font-size:13.5px; margin-bottom:24px;" id="success-message"></p>
            
            <div style="display:flex; justify-content:center; gap:10px;">
                <a id="btn-view-reconstructed" href="#" class="btn-rec btn-rec-primary" target="_blank">
                    <i class="bi bi-eye"></i> Ver en el Sistema
                </a>
                <button type="button" class="btn-rec btn-rec-secondary" onclick="restartWizard()">
                    <i class="bi bi-arrow-repeat"></i> Subir otro PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
const URL_ANALIZAR = '{{ route("ordenes.recuperar.analizar") }}';
const URL_GUARDAR = '{{ route("ordenes.recuperar.guardar") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
const RECOVERY_TYPE = '{{ $type }}';

function toggleTipoRegistro(val) {
    const secInforme = document.getElementById('section-informe');
    if (val === "1") {
        secInforme.style.display = 'block';
    } else {
        secInforme.style.display = 'none';
    }
}

function onFileSelected(input) {
    if (!input.files || input.files.length === 0) return;
    uploadAndAnalyze(input.files[0]);
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize display state based on recovery type
    toggleTipoRegistro(RECOVERY_TYPE === 'informe' ? "1" : "0");

    const dropzone = document.querySelector('.rec-dropzone');
    if (!dropzone) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = 'var(--rec-blue)';
            dropzone.style.background = '#e0f2fe';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.background = 'var(--rec-blue-light)';
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            uploadAndAnalyze(files[0]);
        }
    }, false);
});

async function uploadAndAnalyze(file) {
    const dropzone = document.querySelector('.rec-dropzone');
    const loader = document.getElementById('analyzing-loader');
    
    // Switch views
    dropzone.style.display = 'none';
    loader.style.display = 'block';

    const fd = new FormData();
    fd.append('_token', CSRF_TOKEN);
    fd.append('pdf_file', file);

    try {
        const res = await fetch(URL_ANALIZAR, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        
        if (!data.ok) {
            alert(data.error || 'Error al analizar el PDF.');
            restartWizard();
            return;
        }

        populateForm(data.data, data.advertencia);

    } catch (e) {
        console.error(e);
        alert('Error de conexión con el servidor. Inténtalo de nuevo.');
        restartWizard();
    }
}

function populateForm(o, advertencia) {
    // Enforce recovery mode based on page type
    const isInformeMode = (RECOVERY_TYPE === 'informe');
    document.getElementById('field-is-informe').value = isInformeMode ? "1" : "0";
    toggleTipoRegistro(isInformeMode ? "1" : "0");

    document.getElementById('field-nro-orden').value = o.nro_orden || '';
    document.getElementById('field-nro-factura').value = o.nro_factura || '';
    document.getElementById('field-motivo-ingreso').value = o.motivo_ingreso || 'Servicio Cliente Externo';
    
    document.getElementById('field-cliente-identificacion').value = o.cliente_identificacion || '';
    document.getElementById('field-cliente-nombres').value = o.cliente_nombres || '';
    document.getElementById('field-cliente-apellidos').value = o.cliente_apellidos || '';
    document.getElementById('field-cliente-telefono').value = o.cliente_telefono || '';
    document.getElementById('field-cliente-correo').value = o.cliente_correo || '';
    document.getElementById('field-cliente-direccion').value = o.cliente_direccion || '';

    document.getElementById('field-equipo-tipo').value = o.equipo_tipo || '';
    document.getElementById('field-equipo-marca').value = o.equipo_marca || '';
    document.getElementById('field-equipo-modelo').value = o.equipo_modelo || '';
    document.getElementById('field-equipo-serie').value = o.equipo_serie || '';
    document.getElementById('field-falla').value = o.falla || '';
    document.getElementById('field-observacion').value = o.observacion || '';

    // Autoselect sucursal physical matching prefix
    const nro = o.nro_orden || '';
    const selectSuc = document.getElementById('field-sucursal');
    if (nro.startsWith('GYE')) {
        selectSuc.value = "1"; // Guayaquil
    } else if (nro.startsWith('UIO')) {
        selectSuc.value = "2"; // Quito
    }

    // Try matching technician
    if (o.tecnico_nombre_pdf) {
        const namePdf = String(o.tecnico_nombre_pdf).toLowerCase().trim();
        const selectTec = document.getElementById('field-tecnico');
        Array.from(selectTec.options).forEach(opt => {
            const optName = opt.text.toLowerCase();
            if (optName.includes(namePdf) || namePdf.includes(optName)) {
                selectTec.value = opt.value;
            }
        });
    }

    // Report sections
    document.getElementById('field-estado-equipo').value = o.estado_equipo || 'Operativo';
    document.getElementById('field-antecedentes').value = o.antecedentes || '';
    document.getElementById('field-proceso').value = o.proceso || '';
    document.getElementById('field-conclusion').value = o.conclusion || '';
    document.getElementById('field-recomendaciones').value = o.recomendaciones || '';

    // Warning message
    const warn = document.getElementById('rec-warning-msg');
    if (advertencia) {
        warn.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${advertencia}`;
        warn.style.display = 'flex';
    } else if (o.informe_existente) {
        warn.innerHTML = `<i class="bi bi-exclamation-triangle"></i> La orden <b>${o.nro_orden}</b> y su informe técnico ya existen en el sistema. Guardar actualizará los campos correspondientes.`;
        warn.style.display = 'flex';
    } else if (o.orden_existente) {
        warn.innerHTML = `<i class="bi bi-info-circle"></i> La orden <b>${o.nro_orden}</b> ya existe en el sistema. Guardar asociará el nuevo informe técnico a esta orden.`;
        warn.style.display = 'flex';
    } else {
        warn.style.display = 'none';
    }

    // Move to step 2
    document.getElementById('rec-step-1-content').style.display = 'none';
    document.getElementById('rec-step-2-content').style.display = 'block';
    
    document.getElementById('step-node-1').className = 'rec-step-node completado';
    document.getElementById('step-node-2').className = 'rec-step-node activo';
}

async function submitRecovery() {
    const form = document.getElementById('recovery-form');
    if (!form.reportValidity()) return;

    const btn = document.getElementById('btn-save-recovery');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

    const fd = new FormData(form);

    try {
        const res = await fetch(URL_GUARDAR, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();

        if (!data.ok) {
            alert(data.error || 'Error al guardar los datos.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Reconstruir Registro';
            return;
        }

        // Show Success (Step 3)
        document.getElementById('rec-step-2-content').style.display = 'none';
        document.getElementById('rec-step-3-content').style.display = 'block';

        const isInforme = document.getElementById('field-is-informe').value === "1";
        document.getElementById('success-message').textContent = data.mensaje || 'Registro guardado.';
        
        // Link to view/edit order
        const btnView = document.getElementById('btn-view-reconstructed');
        btnView.href = `{{ url("/operaciones/ordenes/editar") }}/${data.orden_id}`;

        document.getElementById('step-node-2').className = 'rec-step-node completado';
        document.getElementById('step-node-3').className = 'rec-step-node activo';

    } catch (e) {
        console.error(e);
        alert('Error de conexión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save"></i> Reconstruir Registro';
    }
}

function restartWizard() {
    // Reset file input
    document.getElementById('pdf-file-input').value = '';

    // Switch views
    document.getElementById('rec-step-2-content').style.display = 'none';
    document.getElementById('rec-step-3-content').style.display = 'none';
    document.getElementById('rec-step-1-content').style.display = 'block';
    
    document.querySelector('.rec-dropzone').style.display = 'block';
    document.getElementById('analyzing-loader').style.display = 'none';

    document.getElementById('step-node-1').className = 'rec-step-node activo';
    document.getElementById('step-node-2').className = 'rec-step-node';
    document.getElementById('step-node-3').className = 'rec-step-node';
}
</script>
@endpush
