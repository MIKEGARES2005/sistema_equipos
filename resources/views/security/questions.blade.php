@php($esAdministrador = $user->tipo_usuario === 'administrador')

<x-admin-layout>
    @if ($esAdministrador)
        <x-admin-shell>
            @include('security.partials.page-content', ['esAdministrador' => $esAdministrador])
        </x-admin-shell>
    @else
        <div class="min-h-screen bg-slate-50">
            @include('security.partials.page-content', ['esAdministrador' => $esAdministrador])
        </div>
    @endif
</x-admin-layout>
