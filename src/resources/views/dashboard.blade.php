<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-2 h-8 rounded-full bg-gradient-to-b from-sky-400 to-blue-600"></div>
            <div>
                <p class="text-xs text-sky-700 font-semibold">お知らせ</p>
                <h2 class="font-bold text-2xl text-sky-900 leading-tight">Sorairo News</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    @forelse($notes as $note)
                        <div class="relative overflow-hidden rounded-2xl shadow-lg border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-blue-50">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-sky-200/40 blur-3xl"></div>
                            <div class="absolute -left-6 -bottom-12 w-48 h-48 bg-blue-200/30 blur-3xl"></div>

                            <div class="p-5 sm:p-6 flex flex-col gap-4 relative">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-sky-600 font-semibold">
                                            {{ optional($note->published_at ?? $note->created_at)->format('Y/m/d H:i') }}
                                        </p>
                                        <h3 class="text-xl font-bold text-sky-900">{{ $note->title }}</h3>
                                    </div>
                                    @if($note->image_path)
                                        <div class="w-24 h-24 rounded-xl overflow-hidden shadow-md ring-1 ring-sky-100">
                                            <img src="{{ asset('storage/'.$note->image_path) }}" alt="{{ $note->title }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                </div>

                                <p class="text-sky-900/80 leading-relaxed text-sm sm:text-base whitespace-pre-line">{{ $note->content }}</p>

                                <div class="flex items-center gap-3 text-xs text-sky-700">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/80 border border-sky-100 shadow-sm">
                                        <svg class="w-4 h-4 mr-1 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        最新情報
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-8 text-center text-sky-700 shadow-inner">
                            まだお知らせはありません。
                        </div>
                    @endforelse
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-xl p-6 relative overflow-hidden">
                        <div class="absolute inset-0 bg-white/10 mix-blend-overlay"></div>
                        <h3 class="text-xl font-bold">ようこそ</h3>
                        <p class="mt-2 text-sm text-white/90">最新のお知らせを確認してください</p>
                        <div class="mt-4 text-3xl font-black drop-shadow">Sorairo</div>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-white shadow-sm p-5">
                        <h4 class="text-sm font-semibold text-sky-800 mb-2">ヘルプ</h4>
                        <ul class="space-y-1 text-sm text-sky-900/80 list-disc list-inside">
                            <li>メニューから予約を作成</li>
                            <li>マイページで予約の確認・キャンセル</li>
                            <li>お知らせで最新情報をチェック</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
