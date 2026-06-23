@extends('errors.layout')
@section('variant', 'guest')
@section('code', '429')
@section('title', 'Demasiados intentos')
@section('message', 'Has realizado demasiadas solicitudes en poco tiempo.')
@section('detail', 'Espera un momento antes de volver a intentar para que SGN restablezca el acceso de forma segura.')
@section('primary_label', 'Volver al login')
@section('primary_href'){{ route('login') }}@endsection
@section('support_text', 'Si estabas intentando iniciar sesion, espera unos segundos y vuelve a probar con calma.')
