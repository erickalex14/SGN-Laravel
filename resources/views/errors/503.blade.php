@extends('errors.layout')
@section('variant', 'app')
@section('code', '503')
@section('title', 'Servicio temporalmente no disponible')
@section('message', 'SGN esta ocupado o en mantenimiento temporal.')
@section('detail', 'La operacion no esta disponible por el momento. Intenta de nuevo en unos minutos.')
@section('primary_label', 'Ir al dashboard')
@section('primary_href'){{ route('dashboard') }}@endsection
@section('support_text', 'Este estado suele resolverse rapido una vez que termina la tarea interna o se restablece el servicio.')
