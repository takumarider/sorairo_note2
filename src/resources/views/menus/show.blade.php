<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sky-900 leading-tight">
            {{ $menu->name }}
        </h2>
    </x-slot>

        <div class="py-6 sm:py-10"
            x-data="menuOptionSelector({{ $menu->price }}, {{ $menu->is_event ? 0 : $menu->duration }})"
         x-init="init()"
         data-requested-options='@json(array_map("intval", (array) request("options", [])))'>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-28 sm:pb-6">
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-sky-100 overflow-hidden">
                <div class="aspect-[16/9] w-full overflow-hidden bg-gradient-to-br from-sky-50 to-cyan-50">
                    @if($menu->image_path)
                        <img src="{{ asset('storage/' . $menu->image_path) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="text-sky-300 text-xl">画像なし</span>
                        </div>
                    @endif
                </div>

                <div class="p-5 sm:p-8">
                    <p class="text-xs font-semibold tracking-wide text-sky-700">STEP 0 / 2</p>
                    @if($menu->is_event)
                        <p class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">EVENT MENU</p>
                    @endif
                    <h1 class="text-2xl sm:text-3xl font-bold text-sky-950 mt-1 mb-4">{{ $menu->name }}</h1>

                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3">
                            <p class="text-xs text-sky-700 font-semibold">合計料金</p>
                            <div class="flex items-center gap-2 mt-1">
                                <svg class="w-5 h-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-2xl font-bold text-sky-700" x-text="'¥' + Number(totalPriceValue).toLocaleString('ja-JP')"></span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-3">
                            <p class="text-xs text-sky-700 font-semibold">{{ $menu->is_event ? '時間枠' : '合計所要時間' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @if($menu->is_event)
                                    <span class="text-xl font-bold text-sky-900">各開催時間でご案内</span>
                                @else
                                    <span class="text-xl font-bold text-sky-900" x-text="totalDurationValue + '分'"></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 rounded-xl border border-sky-100 bg-white p-4">
                        <h2 class="text-lg sm:text-xl font-bold text-sky-950 mb-2">メニュー詳細</h2>
                        <p class="text-slate-700 leading-relaxed">{{ $menu->description }}</p>
                    </div>

                    @if($menu->is_event)
                        <div class="mb-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            イベントメニューです。日時ごとの枠と定員に応じて予約を受け付けます。
                        </div>
                    @endif

                    @if(! $menu->is_event && $menu->options && $menu->options->isNotEmpty())
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-3">
                                <h2 class="text-lg sm:text-xl font-bold text-sky-950">オプション</h2>
                                <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700" x-text="selectedIds.length + '件選択中'"></span>
                            </div>

                            <div class="mb-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-800">
                                オプションをタップすると選択できます。「追加/解除」を確認できます。
                            </div>

                            <div class="space-y-3">
                                @foreach($menu->options as $option)
                                    <label class="group block cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="options[]"
                                            value="{{ $option->id }}"
                                            data-price="{{ $option->price }}"
                                            data-duration="{{ $option->duration }}"
                                            class="sr-only option-checkbox"
                                            x-model="selectedIds"
                                            @change="onOptionChanged()"
                                        >

                                        <div :class="isSelected({{ $option->id }})
                                            ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200 shadow-sm'
                                            : 'border-sky-100 bg-white hover:border-sky-300 hover:bg-sky-50/40'"
                                            class="rounded-xl border p-3 transition duration-200 group-active:scale-[0.99]">
                                            <div class="flex items-start gap-3">
                                                <div :class="isSelected({{ $option->id }}) ? 'bg-sky-600 border-sky-600' : 'bg-white border-sky-300'"
                                                     class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition">
                                                    <svg x-show="isSelected({{ $option->id }})" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>

                                                <div class="w-16 h-16 overflow-hidden rounded-lg bg-sky-50 shrink-0 ring-1 ring-sky-100">
                                                    @if($option->image_path)
                                                        <img src="{{ asset('storage/' . $option->image_path) }}" alt="{{ $option->name }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-[10px] text-sky-300">画像なし</div>
                                                    @endif
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <p class="font-semibold text-slate-900 leading-tight">{{ $option->name }}</p>
                                                          <span :class="isSelected({{ $option->id }}) ? 'bg-sky-600 text-white ring-sky-600' : 'bg-sky-100 text-sky-800 ring-sky-200'"
                                                              class="inline-flex min-w-[88px] items-center justify-center gap-1 whitespace-nowrap rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 transition">
                                                            <svg x-show="isSelected({{ $option->id }})" class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12l3 3 8-8"></path>
                                                            </svg>
                                                            <span x-text="isSelected({{ $option->id }}) ? '解除' : '追加'"></span>
                                                        </span>
                                                    </div>

                                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                                        <span class="rounded-full bg-sky-100 px-2 py-1 font-semibold text-sky-800">+¥{{ number_format($option->price) }}</span>
                                                        <span class="rounded-full bg-sky-100 px-2 py-1 font-semibold text-sky-800">+{{ $option->duration }}分</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('reservations.calendar') }}" id="reservation-form" @submit="syncHiddenOptions()">
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                        <div id="selected-options-container"></div>

                        <div class="hidden sm:flex gap-3">
                            <button type="submit"
                                    class="flex-1 text-center px-6 py-3.5 rounded-xl bg-sky-500 text-white font-semibold shadow-sm hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2 transition">
                                {{ $menu->is_event ? 'イベント日時を選択する' : '日時を選択して予約へ進む' }}
                            </button>
                            <a href="{{ route('menus.index') }}"
                               class="px-5 py-3.5 rounded-xl bg-sky-50 text-sky-800 font-semibold border border-sky-200 hover:bg-sky-100 transition">
                                戻る
                            </a>
                        </div>

                        <div class="sm:hidden fixed bottom-0 left-0 right-0 z-50 border-t border-sky-100 bg-white/95 backdrop-blur px-4 pt-3 pb-[max(12px,env(safe-area-inset-bottom))] shadow-[0_-8px_24px_rgba(14,165,233,0.18)]">
                            <div class="mb-2 flex items-center justify-between text-xs text-sky-700">
                                <span x-text="selectedIds.length + '件選択中'"></span>
                                <span x-text="'合計 ' + Number(totalPriceValue).toLocaleString('ja-JP') + '円 / ' + totalDurationValue + '分'"></span>
                            </div>
                            <button type="submit"
                                    class="w-full text-center px-6 py-3.5 rounded-xl bg-sky-500 text-white font-semibold shadow-sm active:scale-[0.99] hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300 transition">
                                {{ $menu->is_event ? 'イベント日時を選択する' : '日時を選択して予約へ進む' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function menuOptionSelector(basePrice, baseDuration) {
            return {
                basePrice,
                baseDuration,
                selectedIds: [],
                totalPriceValue: basePrice,
                totalDurationValue: baseDuration,
                init() {
                    this.restoreRequestedOptions();
                    this.updateTotals();
                },
                restoreRequestedOptions() {
                    const root = this.$root;
                    const raw = root ? (root.getAttribute('data-requested-options') || '[]') : '[]';
                    let requested = [];

                    try {
                        requested = JSON.parse(raw);
                    } catch (error) {
                        requested = [];
                    }

                    if (!Array.isArray(requested)) {
                        requested = [];
                    }

                    this.selectedIds = requested.map((id) => Number(id));

                    document.querySelectorAll('.option-checkbox').forEach((checkbox) => {
                        checkbox.checked = this.selectedIds.includes(Number(checkbox.value));
                    });
                },
                isSelected(optionId) {
                    return this.selectedIds.includes(Number(optionId));
                },
                getCheckedOptions() {
                    return Array.from(document.querySelectorAll('.option-checkbox:checked'));
                },
                onOptionChanged() {
                    this.selectedIds = this.getCheckedOptions().map((checkbox) => Number(checkbox.value));
                    this.updateTotals();
                },
                updateTotals() {
                    let totalPrice = this.basePrice;
                    let totalDuration = this.baseDuration;

                    this.getCheckedOptions().forEach((checkbox) => {
                        totalPrice += Number(checkbox.dataset.price || 0);
                        totalDuration += Number(checkbox.dataset.duration || 0);
                    });

                    this.totalPriceValue = totalPrice;
                    this.totalDurationValue = totalDuration;
                },
                syncHiddenOptions() {
                    const container = document.getElementById('selected-options-container');
                    if (!container) {
                        return;
                    }

                    container.innerHTML = '';
                    this.selectedIds.forEach((id) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'options[]';
                        hidden.value = id;
                        container.appendChild(hidden);
                    });
                },
            };
        }
    </script>
</x-app-layout>
