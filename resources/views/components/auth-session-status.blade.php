@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-emerald-200']) }}>
        {{ $status }}
    </div>
@endif
