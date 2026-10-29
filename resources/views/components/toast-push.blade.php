@props(['type' => 'info', 'message' => null, 'duration' => null])

@php
    $payload = ['type' => $type, 'message' => $message];

    if ($duration !== null) {
        $payload['duration'] = (int) $duration;
    }
@endphp

@if (filled($message))
    <div
        x-data
        x-init="$store.toasts.push(@js($payload))"
        class="hidden"
        aria-hidden="true"
    ></div>
@endif
