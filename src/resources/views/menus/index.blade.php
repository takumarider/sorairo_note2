<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            メニュー選択
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="menuList({{ $menus->count() }})">
            <section class="rounded-3xl bg-gradient-to-br from-cyan-50 via-white to-amber-50 p-4 shadow-sm ring-1 ring-slate-200 sm:p-5">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-cyan-700">RESERVATION MENU</p>
                <h1 class="mt-1 text-xl font-bold text-slate-500 sm:text-2xl">メニューを選択してください</h1>
                <p class="mt-1 text-xs text-slate-600">選択したメニューのみ表示します。</p>

                <div class="mt-3 max-w-xs">
                    <label class="block">
                        <span class="text-[11px] font-semibold tracking-wide text-slate-600">メニュー選択</span>
                        <select
                            x-model="selectedMenuId"
                            @change="updateFilteredCount()"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                        >
                            <option value="">すべてのメニューを表示</option>
                            @foreach($menus as $menu)
                                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            @if($menus->count() > 0)
                <div class="mt-6 mb-3 flex items-center justify-between">
                    <p class="text-sm text-slate-600"><span x-text="filteredCount"></span>件のメニュー</p>
                    <p class="text-xs text-slate-500">タップして詳細へ</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($menus as $menu)
                        <article
                            data-menu-card
                            x-show="isVisible({{ $menu->id }})"
                            x-transition.opacity
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"
                        >
                            <div class="relative aspect-[4/3] w-full overflow-hidden">
                                @if($menu->image_path)
                                    <img src="{{ asset('storage/' . $menu->image_path) }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-slate-100">
                                        <span class="text-sm text-slate-400">画像なし</span>
                                    </div>
                                @endif

                                <div class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                    {{ $menu->duration }}分
                                </div>
                            </div>

                            <div class="p-4 sm:p-5">
                                <h2 class="text-lg font-bold text-slate-900">{{ $menu->name }}</h2>
                                <p class="mt-2 min-h-[3rem] text-sm text-slate-600">
                                    {{ \Illuminate\Support\Str::limit((string) $menu->description, 78) }}
                                </p>

                                <div class="mt-4 flex items-center justify-between rounded-xl bg-cyan-50 px-3 py-2">
                                    <span class="text-xs font-semibold text-cyan-700">料金</span>
                                    <span class="text-xl font-bold text-cyan-700">¥{{ number_format($menu->price) }}</span>
                                </div>

                                          <a href="{{ route('menus.show', ['menu' => $menu->id]) }}"
                                              class="mt-4 block w-full rounded-xl bg-sky-500 px-4 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2">
                                    このメニューで日時を選ぶ
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="filteredCount === 0" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-600">
                    条件に合うメニューが見つかりませんでした。検索語や絞り込みを変更してください。
                </div>
            @else
                <div class="mt-6 rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                    <p class="text-base text-slate-500">現在、予約可能なメニューはありません。</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function menuList(totalCount) {
            return {
                selectedMenuId: '',
                totalCount,
                filteredCount: 0,
                init() {
                    this.filteredCount = this.totalCount;
                },
                updateFilteredCount() {
                    this.filteredCount = this.selectedMenuId === '' ? this.totalCount : 1;
                },
                isVisible(menuId) {
                    return this.selectedMenuId === '' || this.selectedMenuId === String(menuId);
                },
            };
        }
    </script>
</x-app-layout>
