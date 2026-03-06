@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1 block text-sm font-medium text-slate-200']) }}>
    {{ $value ?? $slot }}
</label>
