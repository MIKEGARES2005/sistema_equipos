@props([])

@php
    $statusMessages = [
        'profile-updated' => 'Perfil actualizado correctamente.',
        'password-updated' => 'Contraseña actualizada correctamente.',
        'password-reset' => 'Contraseña restablecida correctamente.',
        'verification-link-sent' => 'Te enviamos un nuevo enlace de verificación.',
    ];

    $messages = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
        ['type' => 'success', 'message' => session('status')],
    ])
        ->filter(fn (array $message) => filled($message['message']))
        ->map(function (array $message) use ($statusMessages) {
            $message['message'] = $statusMessages[$message['message']] ?? $message['message'];

            return $message;
        })
        ->values();
@endphp

<div
    x-data
    x-init="$store.toasts.pushMany(@js($messages->all()))"
    class="pointer-events-none fixed inset-x-0 top-5 z-[80] flex justify-center gap-2.5 px-4"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-bind:role="['error', 'warning'].includes(toast.type) ? 'alert' : 'status'"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-2xl border shadow-xl shadow-slate-900/20 ring-1 ring-black/5"
            :class="{
                'border-emerald-300 bg-emerald-50': toast.type === 'success',
                'border-rose-300 bg-rose-50': toast.type === 'error',
                'border-amber-300 bg-amber-50': toast.type === 'warning',
                'border-sky-300 bg-sky-50': toast.type === 'info',
                'border-slate-300 bg-slate-50': !['success', 'error', 'warning', 'info'].includes(toast.type),
            }"
        >
            <div class="flex items-start gap-3 px-4 py-3.5">
                <span
                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                    :class="{
                        'bg-emerald-100 text-emerald-600': toast.type === 'success',
                        'bg-rose-100 text-rose-600': toast.type === 'error',
                        'bg-amber-100 text-amber-600': toast.type === 'warning',
                        'bg-sky-100 text-sky-600': toast.type === 'info',
                        'bg-slate-100 text-slate-500': !['success', 'error', 'warning', 'info'].includes(toast.type),
                    }"
                    aria-hidden="true"
                >
                    <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <svg x-show="toast.type === 'error'" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    <svg x-show="toast.type === 'warning'" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008ZM10.34 3.94 2.7 17.06A1.5 1.5 0 0 0 4 19.31h16a1.5 1.5 0 0 0 1.3-2.25L13.66 3.94a1.5 1.5 0 0 0-2.6 0Z" />
                    </svg>
                    <svg x-show="toast.type === 'info'" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25h.008v.008H12V8.25Zm0 3.75v3.75" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </span>

                <p
                    class="min-w-0 flex-1 pt-1.5 text-sm font-semibold leading-5"
                    :class="{
                        'text-emerald-800': toast.type === 'success',
                        'text-rose-800': toast.type === 'error',
                        'text-amber-800': toast.type === 'warning',
                        'text-sky-800': toast.type === 'info',
                        'text-slate-700': !['success', 'error', 'warning', 'info'].includes(toast.type),
                    }"
                    x-text="toast.message"
                ></p>

                <button
                    type="button"
                    class="-mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    x-on:click="$store.toasts.dismiss(toast.id)"
                    aria-label="Cerrar notificación"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                x-show="toast.duration > 0"
                class="toast-progress absolute inset-x-0 bottom-0 h-0.5 origin-left"
                :class="{
                    'bg-emerald-500': toast.type === 'success',
                    'bg-rose-500': toast.type === 'error',
                    'bg-amber-500': toast.type === 'warning',
                    'bg-sky-500': toast.type === 'info',
                    'bg-slate-400': !['success', 'error', 'warning', 'info'].includes(toast.type),
                }"
                :style="`animation-duration: ${toast.duration}ms`"
            ></div>
        </div>
    </template>
</div>
