@php($esAdministrador = $user->tipo_usuario === 'administrador')

<x-admin-layout>
    @if ($esAdministrador)
        <x-admin-shell>
            @include('profile.partials.page-content', ['esAdministrador' => $esAdministrador])
        </x-admin-shell>
    @else
        <div class="min-h-screen bg-slate-50">
            @include('profile.partials.page-content', ['esAdministrador' => $esAdministrador])
        </div>
    @endif
</x-admin-layout>
