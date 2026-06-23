@extends('errors.layout')
@section('variant', 'app')
@section('code', '500')
@section('title', 'Fallo interno del sistema')
@section('message', 'SGN no pudo completar esta accion.')
@section('detail', 'Se produjo un error interno mientras se procesaba tu solicitud. No mostramos detalles tecnicos por seguridad.')
@section('primary_label', 'Ir al dashboard')
@section('primary_href'){{ route('dashboard') }}@endsection
@section('support_text', 'Vuelve a intentarlo. Si el problema persiste, reporta el flujo exacto al administrador.')
