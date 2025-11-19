@php
    $user = Auth::user();
    $clinic = $user->clinic;
@endphp

@if($clinic && $clinic->logo)
    {{-- Si tu Base64 no tiene prefijo, se lo agregamos --}}
    <img src="data:image/png;base64,{{ $clinic->logo }}" width="60" alt="Logo de {{ $clinic->name }}">
@else
    <img src="{{ asset('assets/images/logoDent.png') }}" width="60" alt="Logo por defecto">
@endif
