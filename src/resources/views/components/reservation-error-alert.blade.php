@props(['message'])

<div role="alert" aria-live="assertive" class="overflow-hidden rounded-2xl border-2 border-red-500 bg-red-50 shadow-lg shadow-red-100">
    <div class="flex items-center gap-2 bg-red-600 px-4 py-2 text-white">
        <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <path d="M12 9v4M12 17h.01" />
        </svg>
        <span class="text-base font-bold sm:text-lg">この時間は予約できません</span>
    </div>
    <div class="px-4 py-3">
        <p class="text-sm font-semibold leading-relaxed text-red-800 sm:text-base">{{ $message }}</p>
        <p class="mt-2 text-xs text-red-700">下の一覧から、別の時間をもう一度お選びください。</p>
    </div>
</div>
