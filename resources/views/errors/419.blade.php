@extends('errors.layout')
@section('variant', 'guest')
@section('code', '419')
@section('title', 'Sesion expirada')
@section('message', 'Tu sesion vencio o el formulario ya no es valido.')
@section('detail', 'Por seguridad, SGN necesita que vuelvas a iniciar sesion antes de continuar con esta operacion.')
@section('primary_label', 'Volver al login')
@section('primary_href'){{ route('login') }}@endsection
@section('support_text', 'Esto suele pasar al dejar una pantalla abierta mucho tiempo o reenviar un formulario anterior.')
