@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block h-12 w-full max-w-none box-border rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
