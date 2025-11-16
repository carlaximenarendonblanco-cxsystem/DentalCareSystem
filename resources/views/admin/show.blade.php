@extends('layouts._partials.layout')
@section('title', __('Información del Usuario'))
@section('subtitle')
{{ __('Información del Usuario') }}
@endsection
@section('content')

<div class="flex justify-end p-5 pb-1">
    <a href="{{ route('admin.users')}}" class="botton1">{{ __('Usuarios') }}</a>
</div>

<div class="bg-white rounded-lg max-w-5xl mx-auto p-6">
    <h1 class="title1 text-center mb-8">{{ __('Detalle del Usuario') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Nombre --}}
        <div>
            <label class="title4 block mb-2">{{ __('Nombre') }}:</label>
            <p class="text-gray-700">{{ $user->name }}</p>
        </div>

        {{-- CI --}}
        <div>
            <label class="title4 block mb-2">{{ __('CI') }}:</label>
            <p class="text-gray-700">{{ $user->ci }}</p>
        </div>

        {{-- Email --}}
        <div>
            <label class="title4 block mb-2">{{ __('Email') }}:</label>
            <p class="text-gray-700">{{ $user->email }}</p>
        </div>

        {{-- Rol --}}
        <div>
            <label class="title4 block mb-2">{{ __('Rol') }}:</label>
            <p class="text-gray-700">{{ ucfirst($user->role) }}</p>
        </div>
        {{-- Creado por --}}
        <div>
            <label class="title4 block mb-2">{{ __('Creado por') }}:</label>
            <p class="text-gray-700">{{ $user->created_by ? App\Models\User::find($user->created_by)->name : __('No disponible') }}</p>
        </div>

        {{-- Editado por --}}
        <div>
            <label class="title4 block mb-2">{{ __('Última edición por') }}:</label>
            <p class="text-gray-700">{{ $user->edit_by ? App\Models\User::find($user->edit_by)->name : __('No disponible') }}</p>
        </div>
    </div>
    @auth
        @if(auth()->user()->role === 'superadmin')
        <!-- Información del sistema -->
        <div class="mt-10 mb-5">
            <h1 class="title1 text-center pb-5">{{ __('Información del Registro') }}</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-4 pb-5">

                <div class="flex gap-2">
                    <h3 class="title4">{{ __('Clínica asignada:') }}</h3>
                    <span class="txt">
                        {{ $user->clinic->name ?? 'Sin clínica' }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <h3 class="title4">{{ __('Creado por:') }}</h3>
                    <span class="txt">
                        {{ $user->creator->name ?? 'N/A' }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <h3 class="title4">{{ __('Última edición por:') }}</h3>
                    <span class="txt">
                        {{ $user->editor->name ?? 'Sin ediciones' }}
                    </span>
                </div>

            </div>
        </div>
        @endif
        @endauth
</div>
@endsection