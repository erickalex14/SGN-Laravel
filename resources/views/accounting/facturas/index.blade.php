@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1500px;">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1"><i class="bi bi-file-earmark-check text-primary me-2"></i>Facturas electrónicas</h1>
                <div class="text-muted">Facturación y trazabilidad desde Caja General y Recuento B2B · SRI PRUEBAS</div>
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
                    <tr><th>Factura</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th><th>Trazabilidad SGN</th><th>Documentos</th></tr>
                </thead>
                <tbody>
                @forelse($result['items'] ?? [] as $invoice)
                    <tr>
                        <td style="min-width:180px">
                            <a href="{{ route('facturas.show', $invoice['id']) }}" class="fw-semibold text-decoration-none">{{ $invoice['externalReference'] }}</a>
                            <div class="small text-muted mt-1">{{ $invoice['issueDate'] }}</div>
                            <div class="small font-monospace">{{ $invoice['sequenceNumber'] ? str_pad($invoice['sequenceNumber'], 9, '0', STR_PAD_LEFT) : '—' }}</div>
                        </td>
                        <td>{{ $invoice['buyerName'] }}<br><small class="text-muted">{{ $invoice['buyerIdentification'] }}</small></td>
                        <td><span class="badge {{ $invoice['status'] === 'AUTHORIZED' ? 'bg-success' : 'bg-secondary' }}">{{ $invoice['status'] }}</span></td>
                        <td class="text-end fw-bold">${{ number_format((float)$invoice['grandTotal'], 2) }}</td>
                        <td style="min-width:390px">
                            @if($trace = ($invoice['traceability'] ?? null))
                                <div class="border rounded-3 p-2 bg-light-subtle">
                                    @foreach($trace['orders'] as $order)
                                        <div class="d-flex justify-content-between gap-2 {{ !$loop->last ? 'border-bottom pb-2 mb-2' : '' }}">
                                            <div>
                                                <div class="fw-semibold text-primary"><i class="bi bi-wrench-adjustable-circle me-1"></i>{{ $order['number'] }}</div>
                                                <small class="text-muted">Técnico: {{ $order['technician'] }}</small>
                                            </div>
                                            <div class="d-flex align-items-start gap-1 flex-wrap justify-content-end">
                                                @if($order['orderUrl'])
                                                    <a href="{{ $order['orderUrl'] }}" target="_blank" class="btn btn-sm btn-outline-primary" title="PDF de la orden"><i class="bi bi-file-earmark-pdf"></i> Orden</a>
                                                @endif
                                                @if($order['reportUrl'])
                                                    <a href="{{ $order['reportUrl'] }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Informe técnico"><i class="bi bi-journal-text"></i> Informe</a>
                                                @else
                                                    <span class="badge text-bg-light border text-muted">Sin informe</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="row g-1 small mt-2">
                                        <div class="col-sm-6"><span class="text-muted">Cobró:</span> <strong>{{ $trace['chargedBy'] }}</strong></div>
                                        <div class="col-sm-6"><span class="text-muted">Fecha/hora:</span> {{ $trace['chargedAt'] }}</div>
                                        <div class="col-sm-6"><span class="text-muted">Cobro:</span> <strong>${{ number_format((float)$trace['amount'], 2) }}</strong></div>
                                        <div class="col-sm-6"><span class="text-muted">Método:</span> {{ $trace['paymentMethods'] ?: 'No registrado' }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">Sin vínculo local de trazabilidad</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap">
                            <div class="d-grid gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('facturas.show', $invoice['id']) }}"><i class="bi bi-eye"></i> Detalle</a>
                                <a class="btn btn-sm btn-outline-success" href="{{ route('facturas.xml', $invoice['id']) }}"><i class="bi bi-code-slash"></i> XML</a>
                                <a class="btn btn-sm btn-outline-danger" href="{{ route('facturas.ride', $invoice['id']) }}"><i class="bi bi-file-pdf"></i> RIDE</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No hay facturas para mostrar.</td></tr>
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
