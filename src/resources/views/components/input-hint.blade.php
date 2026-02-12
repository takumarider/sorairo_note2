@props(['message' => null])

<p {{ $attributes->merge(['class' => 'text-xs text-gray-500 mt-1']) }}>
    @if ($message)
        {{ $message }}
    @else
        {{ $slot }}
    @endif
</p>
