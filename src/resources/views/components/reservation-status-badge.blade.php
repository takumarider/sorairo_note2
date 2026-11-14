@props(['available'])

<span
    class="inline-flex shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-bold leading-none text-white {{ $available ? 'bg-emerald-500' : 'bg-slate-400' }}"
    :class="{ 'bg-emerald-500': sameDayStatus === 'available', 'bg-slate-400': sameDayStatus !== 'available' }"
    x-text="sameDayStatus === 'available' ? '受付中' : (sameDayStatus === 'unavailable' ? '準備中' : (sameDayStatus === 'loading' ? '確認中' : '確認できません'))"
    aria-live="polite"
>{{ $available ? '受付中' : '準備中' }}</span>
