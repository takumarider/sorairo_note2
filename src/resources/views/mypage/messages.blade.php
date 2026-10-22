<x-app-layout>
    <div class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="rounded-2xl bg-sky-50 p-3 shadow-sm sm:p-6">
                <div class="flex flex-nowrap items-center justify-between gap-2">
                    <span class="inline-flex whitespace-nowrap rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">{{ $isArchive ? 'アーカイブ' : 'sorairoメッセージ' }}</span>
                    @if($isArchive)
                        <a href="{{ route('mypage.messages') }}" x-data="{ loading: false }" @click="loading = true" :class="loading ? 'pointer-events-none opacity-60' : ''" class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-semibold text-sky-600 ring-1 ring-sky-200 transition duration-150 hover:bg-sky-100 hover:text-sky-700 active:scale-95 active:bg-sky-200 sm:text-sm"><svg x-cloak x-show="loading" class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>← 一覧</a>
                    @else
                        <a href="{{ route('mypage.messages.archive') }}" x-data="{ loading: false }" @click="loading = true" :class="loading ? 'pointer-events-none opacity-60' : ''" class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-semibold text-sky-600 ring-1 ring-sky-200 transition duration-150 hover:bg-sky-100 hover:text-sky-700 active:scale-95 active:bg-sky-200 sm:text-sm"><svg x-cloak x-show="loading" class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>アーカイブ →</a>
                    @endif
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($commentReservations as $reservation)
                        <a href="{{ route('mypage.reservations.comments', $reservation) }}" class="flex flex-col gap-2 rounded-xl border p-3 shadow-md sm:p-4 {{ $reservation->menu->is_event ? 'border-pink-200 bg-gradient-to-r from-pink-100 to-orange-100' : 'border-sky-100 bg-white' }} transition hover:-translate-y-0.5 hover:shadow-lg sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <div class="min-w-0 flex-1 break-words">
                                <p class="font-semibold text-slate-900">
                                    @if($reservation->menu->is_event)
                                        <span class="mr-1 inline-flex rounded-full bg-gradient-to-r from-pink-500 to-orange-400 px-2 py-0.5 text-[11px] font-bold text-white">イベント</span>
                                    @endif
                                    {{ $reservation->menu->name }}
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $reservation->date?->format('Y/m/d') }} {{ $reservation->start_time?->format('H:i') }}
                                    ・{{ $reservation->status === 'canceled' ? 'キャンセル済み' : ($reservation->status === 'completed' ? '完了' : '予約確定') }}
                                </p>
                                @if($reservation->latestVisibleComment)
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $reservation->latestVisibleComment->body }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center justify-between gap-2 border-t border-sky-50 pt-2 sm:flex-col sm:items-end sm:justify-start sm:border-0 sm:pt-0">
                                @if($reservation->unread_admin_comments_count > 0)
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">{{ $reservation->unread_admin_comments_count }}件の未読</span>
                                @endif
                                <p class="whitespace-nowrap text-sm font-semibold text-sky-600 sm:mt-2">メッセージを見る →</p>
                            </div>
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm font-semibold text-slate-500">{{ $isArchive ? 'アーカイブはありません。' : 'メッセージはありません。' }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
