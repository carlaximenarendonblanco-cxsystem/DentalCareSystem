@php
    $user = Auth::user();
    $clinic = $user->clinic; // relación con Clinic
@endphp

@if($clinic && $clinic->logo)
    <img src="{{ $clinic->logo }}" width="60" alt="Logo de {{ $clinic->name }}">
@else
    <img src="{{ asset('assets/images/logoDent.png') }}" width="60" alt="Logo por defecto">
@endif
