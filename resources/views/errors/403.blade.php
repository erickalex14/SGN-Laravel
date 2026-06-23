@extends('errors.layout')
@section('variant', 'app')
@section('code', '403')
@section('title', 'Acceso restringido')
@section('message', 'No tienes permisos para entrar aqui.')
@section('detail', 'El sistema bloqueo esta pantalla porque tu perfil no tiene autorizacion para este modulo o registro.')
@section('primary_label', 'Ir al dashboard')
@section('primary_href'){{ route('dashboard') }}@endsection
@section('support_text', 'Si deberias tener acceso, solicita revision de permisos al administrador de SGN.')
