@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1500px;">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1"><i class="bi bi-file-earmark-check text-primary me-2"></i>Facturas electrónicas</h1>
                <div class="text-muted">Emisión manual desde Caja General y Recuento B2B · SRI PRUEBAS</div>
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">AMBIENTE DE PRUEBAS</span>
        </div>
    </div>

    @if(session('error') || $error)
        <div class="alert alert-danger">{{ session('error') ?: $error }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Buscar</label>
                    <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Orden, lote, cliente o clave de acceso">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['QUEUED','GENERATED','SIGNED','RECEIVED','AUTHORIZED','RETURNED','REJECTED','ERROR'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filtrar</button></div>
                <div class="col-md-2"><a href="{{ route('facturas.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Referencia</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-end">Total</th><th>Secuencial</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                @forelse($result['items'] ?? [] as $invoice)
                    <tr>
                        <td><a href="{{ route('facturas.show', $invoice['id']) }}" class="fw-semibold text-decoration-none">{{ $invoice['externalReference'] }}</a></td>
                        <td>{{ $invoice['buyerName'] }}<br><small class="text-muted">{{ $invoice['buyerIdentification'] }}</small></td>
                        <td>{{ $invoice['issueDate'] }}</td>
                        <td><span class="badge {{ $invoice['status'] === 'AUTHORIZED' ? 'bg-success' : 'bg-secondary' }}">{{ $invoice['status'] }}</span></td>
                        <td class="text-end fw-bold">${{ number_format((float)$invoice['grandTotal'], 2) }}</td>
                        <td>{{ $invoice['sequenceNumber'] ? str_pad($invoice['sequenceNumber'], 9, '0', STR_PAD_LEFT) : '—' }}</td>
                        <td style="white-space:nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('facturas.show', $invoice['id']) }}">Detalle</a>
                            <a class="btn btn-sm btn-outline-success" href="{{ route('facturas.xml', $invoice['id']) }}"><i class="bi bi-code-slash"></i> XML</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('facturas.ride', $invoice['id']) }}"><i class="bi bi-file-pdf"></i> RIDE</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No hay facturas para mostrar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(($result['totalPages'] ?? 0) > 1)
            <div class="card-body border-top d-flex justify-content-between">
                <span class="text-muted">{{ $result['totalItems'] }} facturas</span>
                <div class="btn-group">
                    @for($page = 1; $page <= $result['totalPages']; $page++)
                        <a class="btn btn-sm {{ $page == ($result['page'] ?? 1) ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">{{ $page }}</a>
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
