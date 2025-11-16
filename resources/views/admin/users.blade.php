@extends('layouts._partials.layout')
@section('title', __('Usuarios'))
@section('subtitle')
{{ __('Usuarios') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Buscador -->
    <form method="POST" action="{{ route('admin.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar usuario...') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs" />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.create') }}" class="botton1">{{ __('Crear Usuario') }}</a>
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Usuarios registrados') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-4 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Nombre') }}</div>
        <div class="title4">{{ __('Email') }}</div>
        <div class="title4">{{ __('Rol') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($users as $user)
    <div class="grid grid-cols-4 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('admin.show', $user->id) }}" class="hover:text-cyan-600">{{ $user->name }}</a></div>
        <div><a href="{{ route('admin.show', $user->id) }}" class="hover:text-cyan-600">{{ $user->email }}</a></div>
        <div><a href="{{ route('admin.show', $user->id) }}" class="hover:text-cyan-600">{{ ucfirst($user->role) }}</a></div>
        <div class="flex justify-center gap-2">
            @auth
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')
                <a href="{{ route('admin.edit', $user->id) }}" class="botton3">{{ __('Editar') }}</a>
                <form method="POST" action="{{ route('admin.destroy', $user->id) }}"
                      onsubmit="return confirm('{{ __('¿Seguro que deseas eliminar este usuario?') }}');">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se registraron usuarios.') }}</p>
    @endforelse

    <div class="pt-4">{{ $users->links() }}</div>
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($users as $user)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $user->name }}</div>
            <div class="text-gray-500 text-sm">{{ ucfirst($user->role) }}</div>
        </div>
        <div class="text-gray-600">{{ $user->email }}</div>
        <div class="flex gap-2 mt-2">
            @auth
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')
                <a href="{{ route('admin.edit', $user->id) }}" class="botton3 flex-1 text-center">{{ __('Editar') }}</a>
                <form method="POST" action="{{ route('admin.destroy', $user->id) }}" 
                      onsubmit="return confirm('{{ __('¿Seguro que deseas eliminar este usuario?') }}');" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete w-full cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se registraron usuarios.') }}</p>
    @endforelse

    <div class="pt-4">{{ $users->links() }}</div>
</div>
@endsection
