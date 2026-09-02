<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Novitec SGN Mobile (Android App)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
});

Route::prefix('v1')->middleware('auth.api')->group(function () {
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::post('/auth/fcm-token', [AuthApiController::class, 'registerFcmToken']);

    Route::get('/catalogo', [TicketApiController::class, 'catalogo']);
    Route::get('/auditoria', [TicketApiController::class, 'auditoria']);
    Route::get('/solicitantes', [TicketApiController::class, 'solicitantes']);

    Route::get('/tickets', [TicketApiController::class, 'index']);
    Route::post('/tickets', [TicketApiController::class, 'store']);
    Route::get('/tickets/{id}', [TicketApiController::class, 'show']);
    Route::post('/tickets/{id}/chat', [TicketApiController::class, 'chat']);
    Route::get('/tickets/{id}/chat/sync', [TicketApiController::class, 'syncChat']);
    Route::post('/tickets/{id}/calificar', [TicketApiController::class, 'calificar']);
    Route::post('/tickets/{id}/reabrir', [TicketApiController::class, 'reabrir']);

    Route::post('/tickets/{id}/cambiar-estado', [TicketApiController::class, 'cambiarEstado']);
    Route::post('/tickets/{id}/asignar', [TicketApiController::class, 'asignar']);

    // Paridad con el módulo web de tickets.
    Route::get('/perfil', [TicketApiController::class, 'perfil']);
    Route::post('/perfil', [TicketApiController::class, 'guardarPerfil']);

    Route::post('/solicitantes', [TicketApiController::class, 'solicitanteStore']);
    Route::post('/solicitantes/{id}', [TicketApiController::class, 'solicitanteUpdate']);

    Route::get('/auditoria/{id}/detalle', [TicketApiController::class, 'auditoriaDetalle']);

    // Devuelve enlaces firmados; la descarga en sí va por las rutas de abajo.
    Route::get('/tickets/{id}/documentos', [TicketApiController::class, 'documentos']);
});

/*
 * Documentos del ticket para el móvil.
 *
 * Van fuera del grupo autenticado a propósito: el navegador del teléfono abre estas URLs sin
 * el encabezado Authorization. La protección es la firma temporal, que solo emite
 * TicketApiController::documentos tras validar el token y el permiso sobre el ticket.
 */
Route::middleware('signed')->prefix('v1/documentos/ticket')->group(function () {
    Route::get('/{id}/imprimir', [TicketApiController::class, 'documentoImprimir'])
        ->name('api.tickets.documento.imprimir');
    Route::get('/{id}/word-mba', [TicketApiController::class, 'documentoWordMba'])
        ->name('api.tickets.documento.word');
});
