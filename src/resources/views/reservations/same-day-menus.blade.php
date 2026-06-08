<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            今日の予約
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-cyan-50 to-sky-50 p-4 ring-1 ring-cyan-100 sm:p-5">
                    <p class="text-xs font-semibold tracking-wide text-cyan-700">SAME DAY RESERVATION</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $date->isoFormat('Y年M月D日(ddd)') }} {{ $startTime }} スタート</h1>
                    <p class="mt-2 text-sm text-slate-600">この時間で予約可能な通常メニューを表示しています。オプションを選択して確認へ進んでください。</p>
                </div>

                @if($menus->isEmpty())
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        この時間で予約可能な通常メニューはありません。別の時間をお試しください。
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($menus as $menu)
                            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                                <div class="relative aspect-[4/3] w-full overflow-hidden">
                                    @if($menu->image_path)
                                        <img src="{{ Storage::url($menu->image_path) }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-slate-100">
                                            <span class="text-sm text-slate-400">画像なし</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4 sm:p-5">
                                    <h2 class="text-lg font-bold text-slate-900">{{ $menu->name }}</h2>
                                    <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit((string) $menu->description, 86) }}</p>

                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div class="rounded-lg bg-cyan-50 px-3 py-2 text-cyan-800">
                                            <p class="text-[11px] font-semibold">料金</p>
                                            <p class="font-bold">¥{{ number_format($menu->price) }}</p>
                                        </div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-slate-800">
                                            <p class="text-[11px] font-semibold">所要時間</p>
                                            <p class="font-bold">{{ $menu->duration }}分</p>
                                        </div>
                                    </div>

                                    <form method="GET" action="{{ route('reservations.confirm') }}" class="mt-4 space-y-3">
                                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                        <input type="hidden" name="start_time" value="{{ $startTime }}">

                                        @if($menu->options->isNotEmpty())
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                                <p class="mb-2 text-xs font-semibold text-slate-700">オプションを選択</p>
                                                <div class="space-y-2">
                                                    @foreach($menu->options as $option)
                                                        <label class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                                            <span>{{ $option->name }}</span>
                                                            <span class="text-xs text-slate-500">+¥{{ number_format($option->price) }} / +{{ $option->duration }}分</span>
                                                            <input type="checkbox" name="options[]" value="{{ $option->id }}" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <button type="submit"
                                                class="w-full rounded-xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2">
                                            このメニューで確認へ進む
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('reservations.same-day.times') }}" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                        ← 時間選択に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
