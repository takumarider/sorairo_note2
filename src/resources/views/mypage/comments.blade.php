<x-app-layout>
    @php($isEvent = (bool) $reservation->menu->is_event)
    <div class="py-6 sm:py-10">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            <div class="rounded-2xl p-4 shadow-sm sm:p-5 {{ $isEvent ? 'border border-pink-200 bg-gradient-to-r from-pink-100 to-orange-100' : 'bg-white' }}">
                <a href="{{ route('mypage.messages') }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700">← メッセージ一覧へ戻る</a>
                <h1 class="mt-3 break-words text-lg font-bold text-slate-900 sm:text-xl">{{ $reservation->menu->name }}</h1>
                <p class="mt-1 break-words text-xs text-slate-600 sm:text-sm">
                    {{ $reservation->date?->isoFormat('Y年M月D日(ddd)') }}
                    {{ $reservation->start_time?->format('H:i') }} - {{ $reservation->end_time?->format('H:i') }}
                    ・{{ $reservation->status === 'canceled' ? 'キャンセル済み' : ($reservation->status === 'completed' ? '完了' : '予約確定') }}
                </p>
            </div>

            @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show" x-transition.opacity.duration.300ms x-cloak role="status" class="fixed left-1/2 top-4 z-50 -translate-x-1/2 rounded-full bg-gradient-to-r from-orange-400 via-pink-400 to-orange-300 px-5 py-2.5 text-sm font-bold text-white shadow-lg ring-2 ring-white/70"
                     style="background-image: linear-gradient(115deg, #fb923c 0%, #f472b6 30%, #fdba74 55%, #ec4899 80%, #fb923c 100%);">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl border p-3 shadow-sm sm:p-5 {{ $isEvent ? 'border-pink-100 bg-orange-50' : 'border-sky-100 bg-sky-50' }}" x-data="{ deleteUrl: null, preview: '' }" @keydown.escape.window="deleteUrl = null">
                <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-bold text-sky-700 ring-1 ring-sky-200">メッセージ履歴</span>
                <div class="mt-3 space-y-3 sm:mt-4 sm:space-y-4">
                    @forelse($comments as $comment)
                        @php($isMine = $comment->sender_type === \App\Models\ReservationComment::SENDER_USER)
                        <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            <div class="min-w-0 max-w-[92%] sm:max-w-[80%]">
                                <p class="mb-1 text-xs font-bold text-sky-800 {{ $isMine ? 'text-right' : 'text-left' }}">{{ $isMine ? 'あなた' : 'サロン' }}</p>
                                @if($isMine)
                                    <button type="button" @click="deleteUrl = '{{ route('mypage.reservations.comments.destroy', [$reservation, $comment]) }}'; preview = @js($comment->body)" class="block w-full text-left rounded-2xl rounded-tr-sm bg-gradient-to-br {{ $isEvent ? 'from-pink-500 to-orange-400' : 'from-sky-400 to-blue-500' }} px-3 py-2 text-white shadow-sm active:opacity-80 sm:px-4 sm:py-2.5">
                                        <span class="block whitespace-pre-wrap break-words [overflow-wrap:anywhere] text-sm leading-relaxed">{{ $comment->body }}</span>
                                    </button>
                                @else
                                    <div class="px-3 py-2 shadow-sm sm:px-4 sm:py-2.5 {{ $isMine ? 'rounded-2xl rounded-tr-sm bg-gradient-to-br '.($isEvent ? 'from-pink-500 to-orange-400' : 'from-sky-400 to-blue-500').' text-white' : 'rounded-2xl rounded-tl-sm border border-sky-200 bg-gradient-to-br from-white to-sky-100 text-slate-700' }}">
                                        <p class="whitespace-pre-wrap break-words [overflow-wrap:anywhere] text-sm leading-relaxed">{{ $comment->body }}</p>
                                    </div>
                                @endif
                                <time class="mt-1 block text-xs text-sky-600 {{ $isMine ? 'text-right' : 'text-left' }}">{{ $comment->created_at->timezone('Asia/Tokyo')->format('Y/m/d') }}</time>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-sky-700">メッセージはまだありません。</p>
                    @endforelse
                </div>

                <div x-cloak x-show="deleteUrl" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="deleteUrl = null">
                    <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl">
                        <p class="text-base font-bold text-slate-900">このメッセージを削除しますか？</p>
                        <p class="mt-2 line-clamp-3 break-words rounded-lg bg-slate-50 p-3 text-sm text-slate-600" x-text="preview"></p>
                        <form method="POST" :action="deleteUrl" class="mt-4 flex gap-2">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="deleteUrl = null" class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">キャンセル</button>
                            <button type="submit" class="flex-1 rounded-lg bg-rose-500 px-4 py-2 text-sm font-bold text-white hover:bg-rose-600">削除</button>
                        </form>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('mypage.reservations.comments.store', $reservation) }}" class="rounded-2xl border border-sky-100 bg-white p-3 shadow-sm" x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                @csrf
                <div class="flex items-end gap-2">
                    <label for="body" class="sr-only">メッセージ</label>
                    <textarea id="body" name="body" rows="2" maxlength="1000" required placeholder="サロンへメッセージを入力" class="min-w-0 flex-1 resize-none rounded-xl border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('body') }}</textarea>
                    <button type="submit" :disabled="submitting" class="shrink-0 whitespace-nowrap rounded-xl bg-gradient-to-r {{ $isEvent ? 'from-pink-500 to-orange-400' : 'from-sky-400 to-blue-500' }} px-4 py-2.5 text-sm font-bold text-white shadow-sm {{ $isEvent ? 'hover:from-pink-600 hover:to-orange-500' : 'hover:from-sky-500 hover:to-blue-600' }} disabled:cursor-not-allowed disabled:opacity-60">
                        <span x-show="!submitting">送信</span>
                        <span x-cloak x-show="submitting">送信中…</span>
                    </button>
                </div>
                @error('body')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
</x-app-layout>
