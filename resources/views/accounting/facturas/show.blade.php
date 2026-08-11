@extends('layouts.app')

@section('contenido')
<div class="container-fluid px-4 py-4" style="max-width: 1300px;">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ route('facturas.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Facturas</a>
            <h1 class="h4 fw-bold mt-2 mb-0">{{ $invoice['externalReference'] }}</h1>
        </div>
        <div>
            <a class="btn btn-outline-success" href="{{ route('facturas.xml', $invoice['id']) }}"><i class="bi bi-code-slash me-1"></i>Descargar XML</a>
            <a class="btn btn-danger" href="{{ route('facturas.ride', $invoice['id']) }}"><i class="bi bi-file-pdf me-1"></i>Descargar RIDE</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h2 class="h6 fw-bold">Comprobante</h2>
            <div class="row g-2">
                <div class="col-md-6"><small class="text-muted">Cliente</small><div class="fw-semibold">{{ $invoice['buyerName'] }}</div><div>{{ $invoice['buyerIdentification'] }}</div></div>
                <div class="col-md-3"><small class="text-muted">Estado</small><div><span class="badge bg-primary">{{ $invoice['status'] }}</span></div></div>
                <div class="col-md-3"><small class="text-muted">Total</small><div class="fs-5 fw-bold">${{ number_format((float)$invoice['grandTotal'], 2) }}</div></div>
                <div class="col-12"><small class="text-muted">Clave de acceso</small><div class="font-monospace text-break">{{ $invoice['accessKey'] ?? 'Pendiente' }}</div></div>
                <div class="col-12"><small class="text-muted">Autorización</small><div class="font-monospace text-break">{{ $invoice['authorizationNumber'] ?? 'Pendiente' }}</div></div>
            </div>
        </div></div></div>
        <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h2 class="h6 fw-bold">Totales</h2>
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format((float)$invoice['subtotal'], 2) }}</strong></div>
            <div class="d-flex justify-content-between"><span>IVA</span><strong>${{ number_format((float)$invoice['taxTotal'], 2) }}</strong></div>
            <hr><div class="d-flex justify-content-between fs-5"><span>Total</span><strong>${{ number_format((float)$invoice['grandTotal'], 2) }}</strong></div>
        </div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-3"><div class="card-body">
        <h2 class="h6 fw-bold">Historial auditable</h2>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Fecha</th><th>Estado</th><th>Motivo</th><th>Actor</th></tr></thead><tbody>
            @foreach($invoice['statusHistory'] ?? [] as $event)
                <tr><td>{{ $event['occurredAt'] }}</td><td>{{ $event['status'] }}</td><td>{{ $event['reason'] }}</td><td>{{ $event['actorName'] }}</td></tr>
            @endforeach
        </tbody></table></div>
    </div></div>

    <div class="card border-0 shadow-sm"><div class="card-body">
        <h2 class="h6 fw-bold">Intercambios y respuestas SRI</h2>
        @forelse($invoice['sriAttempts'] ?? [] as $attempt)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between"><strong>{{ $attempt['stage'] }} · intento {{ $attempt['attemptNumber'] }}</strong><span class="badge bg-secondary">{{ $attempt['sriStatus'] ?? $attempt['transportStatus'] ?? 'PENDIENTE' }}</span></div>
                @if($attempt['error'])<div class="text-danger mt-2">{{ $attempt['error'] }}</div>@endif
                @foreach($attempt['messages'] ?? [] as $message)
                    <div class="alert alert-warning mt-2 mb-0"><strong>{{ $message['identifier'] ?? $message['type'] }}:</strong> {{ $message['message'] }} @if($message['additionalInformation'])<br><small>{{ $message['additionalInformation'] }}</small>@endif</div>
                @endforeach
            </div>
        @empty
            <div class="text-muted">Aún no existen intercambios registrados con el SRI.</div>
        @endforelse
    </div></div>
</div>
@endsection
