@extends('layouts.app')

@section('titulo', 'Mi Cuenta')

@push('css_adicional')
<style>
.cfg-container{max-width:560px;margin:0 auto;padding:28px 24px;}
.form-titulo h2{font-size:20px;font-weight:800;color:#0f172a;margin:0 0 4px;}
.form-titulo p{color:#94a3b8;font-size:13px;margin:0 0 24px;}
.cfg-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;overflow:hidden;border:1.5px solid #e2e8f0;}
.cfg-card-danger{border-color:#fecaca;}
.cfg-card-header{display:flex;align-items:center;gap:16px;padding:20px 22px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-bottom:1px solid #bfdbfe;}
.cfg-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;}
.cfg-user-name{font-size:16px;font-weight:700;color:#0f172a;}
.cfg-user-sub{font-size:12px;color:#64748b;margin-top:3px;}
.cfg-rol-badge{display:inline-block;background:#dbeafe;color:#1e40af;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:700;}
.cfg-section-title{font-size:13px;font-weight:700;color:#374151;padding:16px 22px 0;display:flex;align-items:center;}
.cfg-section-title i{color:#2563eb;}
.cfg-body{padding:16px 22px 20px;}
.cfg-campo{display:flex;flex-direction:column;gap:5px;margin-bottom:16px;}
.cfg-campo label{font-size:13px;font-weight:600;color:#374151;}
.cfg-campo input{border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;background:#f8fafc;}
.cfg-campo input:focus{outline:none;border-color:#2563eb;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.cfg-input-eye{position:relative;}
.cfg-input-eye input{padding-right:42px;}
.cfg-input-eye button{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;}
.cfg-error{display:none;font-size:11.5px;color:#dc2626;font-weight:500;}
.cfg-btn{display:inline-flex;align-items:center;border:none;padding:10px 22px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;}
.cfg-btn:disabled{opacity:.6;cursor:not-allowed;}
.cfg-btn-primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;}
.cfg-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;}
.cfg-btn-ghost{background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;}
.cfg-danger-txt{font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.6;}
.cfg-msg{display:none;align-items:center;gap:8px;margin-top:14px;padding:11px 16px;border-radius:8px;font-size:13px;font-weight:600;}
.cfg-msg-ok{background:#ecfdf5;color:#065f46;border:1px solid #6ee7b7;}
.cfg-msg-err{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;}
.cfg-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;z-index:9999;}
.cfg-modal-box{background:#fff;border-radius:16px;padding:36px 32px;text-align:center;max-width:360px;width:92%;}
.cfg-modal-icon{font-size:40px;color:#ef4444;margin-bottom:14px;}
.cfg-modal-box h3{font-size:18px;font-weight:800;color:#0f172a;margin:0 0 8px;}
.cfg-modal-box p{font-size:13.5px;color:#64748b;margin:0 0 24px;}
.cfg-modal-btns{display:flex;flex-direction:column;gap:10px;}
</style>
@endpush

@section('contenido')
<section class="modulo activo">
<div class="cfg-container">
    <div class="form-titulo">
        <h2><i class="bi bi-person-circle me-2"></i>Mi Cuenta</h2>
        <p>Administra tu cuenta y preferencias</p>
    </div>

    <div class="cfg-card">
        <div class="cfg-card-header">
            <div class="cfg-avatar">{{ strtoupper(substr($usuario_actual, 0, 1)) }}</div>
            <div>
                <div class="cfg-user-name">{{ $nombre_actual ?: $usuario_actual }}</div>
                <div class="cfg-user-sub">@{{ $usuario_actual }} &middot;
                    <span class="cfg-rol-badge">{{ $grupo_nombre }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="cfg-card">
        <div class="cfg-section-title"><i class="bi bi-person-badge me-2"></i>Nombre de usuario</div>
        <div class="cfg-body">
            <div class="cfg-campo">
                <label>Nombre completo</label>
                <input type="text" id="cfg-nombre" value="{{ $nombre_actual }}" maxlength="100" autocomplete="off">
                <span class="cfg-error" id="cfg-nombre-err"></span>
            </div>
            <div class="cfg-campo">
                <label>Telefono</label>
                <input type="text" id="cfg-telefono" value="{{ $telefono_actual }}" maxlength="15" autocomplete="off">
            </div>
            <div class="cfg-campo">
                <label>Correo electronico</label>
                <input type="email" id="cfg-correo" value="{{ $correo_actual }}" maxlength="100" autocomplete="off">
            </div>
            <button class="cfg-btn cfg-btn-primary" onclick="guardarPerfil()">
                <i class="bi bi-floppy me-2"></i>Guardar datos
            </button>
            <div class="cfg-msg" id="cfg-msg-nombre"></div>
        </div>
    </div>

    <div class="cfg-card">
        <div class="cfg-section-title"><i class="bi bi-lock me-2"></i>Cambiar contrasena</div>
        <div class="cfg-body">
            <div class="cfg-campo">
                <label>Contrasena actual</label>
                <div class="cfg-input-eye">
                    <input type="password" id="cfg-pass-actual" maxlength="12">
                    <button type="button" onclick="toggleCfgEye('cfg-pass-actual','eye1')"><i class="bi bi-eye" id="eye1"></i></button>
                </div>
                <span class="cfg-error" id="cfg-pass-actual-err"></span>
            </div>
            <div class="cfg-campo">
                <label>Nueva contrasena</label>
                <div class="cfg-input-eye">
                    <input type="password" id="cfg-pass-nueva" maxlength="12">
                    <button type="button" onclick="toggleCfgEye('cfg-pass-nueva','eye2')"><i class="bi bi-eye" id="eye2"></i></button>
                </div>
                <span class="cfg-error" id="cfg-pass-nueva-err"></span>
            </div>
            <div class="cfg-campo">
                <label>Confirmar nueva contrasena</label>
                <div class="cfg-input-eye">
                    <input type="password" id="cfg-pass-confirm" maxlength="12">
                    <button type="button" onclick="toggleCfgEye('cfg-pass-confirm','eye3')"><i class="bi bi-eye" id="eye3"></i></button>
                </div>
                <span class="cfg-error" id="cfg-pass-confirm-err"></span>
            </div>
            <button class="cfg-btn cfg-btn-primary" onclick="guardarPassword()">
                <i class="bi bi-shield-lock me-2"></i>Cambiar contrasena
            </button>
            <div class="cfg-msg" id="cfg-msg-pass"></div>
        </div>
    </div>

    <div class="cfg-card cfg-card-danger">
        <div class="cfg-section-title"><i class="bi bi-box-arrow-right me-2"></i>Sesion</div>
        <div class="cfg-body">
            <p class="cfg-danger-txt">Al cerrar sesion deberas volver a ingresar tus credenciales.</p>
            <button class="cfg-btn cfg-btn-danger" onclick="confirmarLogout()">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesion
            </button>
        </div>
    </div>
</div>
</section>

<div id="cfg-modal-logout" class="cfg-modal-overlay" style="display:none;">
    <div class="cfg-modal-box">
        <div class="cfg-modal-icon"><i class="bi bi-box-arrow-right"></i></div>
        <h3>Cerrar sesion</h3>
        <p>Estas seguro de que deseas salir del sistema?</p>
        <div class="cfg-modal-btns">
            <button class="cfg-btn cfg-btn-danger" onclick="window.location.href='{{ route('auth.logout') }}'">Si, cerrar sesion</button>
            <button class="cfg-btn cfg-btn-ghost" onclick="document.getElementById('cfg-modal-logout').style.display='none'">Cancelar</button>
        </div>
    </div>
</div>
@endsection

@push('js_adicional')
<script>
function toggleCfgEye(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'bi bi-eye'; }
}
function cfgMsg(elId, tipo, txt) {
    var el = document.getElementById(elId);
    el.className = 'cfg-msg cfg-msg-' + tipo;
    el.innerHTML = (tipo === 'ok' ? '<i class="bi bi-check-circle me-2"></i>' : '<i class="bi bi-exclamation-circle me-2"></i>') + txt;
    el.style.display = 'flex';
}
function cfgErr(elId, txt) {
    var el = document.getElementById(elId);
    el.textContent = txt;
    el.style.display = txt ? 'block' : 'none';
}
async function guardarPerfil() {
    var nombre = document.getElementById('cfg-nombre').value.trim();
    var telefono = document.getElementById('cfg-telefono').value.trim();
    var correo = document.getElementById('cfg-correo').value.trim();
    cfgErr('cfg-nombre-err', '');
    if (!nombre) { cfgErr('cfg-nombre-err', 'El nombre no puede estar vacio.'); return; }
    if (nombre.length < 3) { cfgErr('cfg-nombre-err', 'Minimo 3 caracteres.'); return; }
    if (document.getElementById('cfg-telefono').classList.contains('is-invalid') ||
        document.getElementById('cfg-correo').classList.contains('is-invalid')) {
        cfgMsg('cfg-msg-nombre', 'err', 'Por favor, corrija los errores en los campos antes de guardar.');
        return;
    }
    var fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('accion', 'perfil');
    fd.append('nombre', nombre);
    fd.append('telefono', telefono);
    fd.append('correo', correo);
    try {
        var r = await fetch('{{ route("mi_cuenta.guardar") }}', { method: 'POST', body: fd });
        var d = await r.json();
        if (d.ok) cfgMsg('cfg-msg-nombre', 'ok', d.mensaje);
        else cfgMsg('cfg-msg-nombre', 'err', d.error || 'No se pudo guardar.');
    } catch (e) {
        cfgMsg('cfg-msg-nombre', 'err', 'Error de conexion.');
    }
}
async function guardarPassword() {
    var actual = document.getElementById('cfg-pass-actual').value;
    var nueva = document.getElementById('cfg-pass-nueva').value;
    var confirm = document.getElementById('cfg-pass-confirm').value;
    cfgErr('cfg-pass-actual-err', '');
    cfgErr('cfg-pass-nueva-err', '');
    cfgErr('cfg-pass-confirm-err', '');
    if (!actual) { cfgErr('cfg-pass-actual-err', 'Ingresa tu contrasena actual.'); return; }
    if (nueva.length < 6 || nueva.length > 12) { cfgErr('cfg-pass-nueva-err', 'Debe tener entre 6 y 12 caracteres.'); return; }
    if (nueva !== confirm) { cfgErr('cfg-pass-confirm-err', 'Las contrasenas no coinciden.'); return; }
    var fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('accion', 'password');
    fd.append('actual', actual);
    fd.append('nueva', nueva);
    try {
        var r = await fetch('{{ route("mi_cuenta.guardar") }}', { method: 'POST', body: fd });
        var d = await r.json();
        if (d.ok) {
            cfgMsg('cfg-msg-pass', 'ok', d.mensaje);
            document.getElementById('cfg-pass-actual').value = '';
            document.getElementById('cfg-pass-nueva').value = '';
            document.getElementById('cfg-pass-confirm').value = '';
        } else {
            cfgMsg('cfg-msg-pass', 'err', d.error || 'No se pudo cambiar.');
        }
    } catch (e) {
        cfgMsg('cfg-msg-pass', 'err', 'Error de conexion.');
    }
}
function confirmarLogout() {
    document.getElementById('cfg-modal-logout').style.display = 'flex';
}
document.addEventListener('DOMContentLoaded', () => {
    setupDynamicValidation(document.getElementById('cfg-telefono'), EcuadorianValidator.validarTelefono, (v) => {
        if (/[^\d]/.test(v)) return 'El teléfono sólo debe contener números.';
        return 'El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) de Ecuador.';
    });
    setupDynamicValidation(document.getElementById('cfg-correo'), EcuadorianValidator.validarEmail, (v) => {
        return 'El correo electrónico no tiene un formato válido.';
    });
});
</script>
@endpush

