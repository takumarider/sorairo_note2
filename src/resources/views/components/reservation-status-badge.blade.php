@props(['available'])

<span
    aria-hidden="true"
    class="inline-flex shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-bold leading-none {{ $available ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white' }}"
>
    {{ $available ? '受付中' : '準備中' }}
</span>
