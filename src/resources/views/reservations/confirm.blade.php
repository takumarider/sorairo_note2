<x-app-layout>
    @php
        $isEvent = (bool) $menu->is_event;
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl {{ $isEvent ? 'text-violet-900' : 'text-gray-800' }} leading-tight">
            {{ __('予約内容確認') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-8">予約内容を確認してください</h1>

                <!-- メニュー -->
                <div class="border-b border-gray-200 py-4">
                    <p class="text-sm text-gray-600 mb-1">メニュー</p>
                    <p class="text-xl font-bold text-gray-900">{{ $menu->name }}</p>
                </div>

                <!-- オプション -->
                @if(!empty($options) && $options->isNotEmpty())
                <div class="border-b border-gray-200 py-4">
                    <p class="text-sm text-gray-600 mb-2">オプション</p>
                    <div class="space-y-2">
                        @foreach($options as $option)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 overflow-hidden rounded bg-gray-100 shrink-0">
                                        @if($option->image_path)
                                            <img src="{{ Storage::url($option->image_path) }}" alt="{{ $option->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">画像なし</div>
                                        @endif
                                    </div>
                                    <span class="text-gray-700">{{ $option->name }}</span>
                                </div>
                                <span class="text-gray-700">+¥{{ number_format($option->price) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 日時 -->
                <div class="border-b border-gray-200 py-4">
                    <p class="text-sm text-gray-600 mb-1">日時</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->isoFormat('Y年M月D日(dddd)') }}
                        {{ $startTime }} - {{ $endTime }}
                    </p>
                </div>

                <!-- 所要時間 -->
                <div class="border-b border-gray-200 py-4">
                    <p class="text-sm text-gray-600 mb-1">所要時間</p>
                    <p class="text-gray-900">{{ $totalDuration }}分</p>
                </div>

                <!-- メニュー詳細 -->
                @if($menu->description)
                <div class="border-b border-gray-200 py-4">
                    <p class="text-sm text-gray-600 mb-2">メニュー詳細</p>
                    <p class="text-gray-700 leading-relaxed">{{ $menu->description }}</p>
                </div>
                @endif

                <!-- 料金 -->
                <div class="rounded-lg p-6 mt-8 mb-8 {{ $isEvent ? 'reservation-flow-price-card--event' : 'bg-blue-50' }}">
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-semibold text-gray-900">合計料金</p>
                        <p class="text-3xl font-bold {{ $isEvent ? 'text-violet-700' : 'text-blue-600' }}">¥{{ number_format($totalPrice) }}</p>
                    </div>
                </div>

                <!-- 予約ボタン -->
                <form method="POST"
                        action="{{ route('reservations.store') }}"
                    data-loading-overlay="true"
                    data-loading-message="予約を確定しています。完了までそのままお待ちください。"
                        x-data="{ submitting: false }"
                        @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                    @csrf
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <input type="hidden" name="start_time" value="{{ $startTime }}">
                    @if(!empty($slotId))
                        <input type="hidden" name="slot_id" value="{{ $slotId }}">
                    @endif
                    @foreach($options ?? [] as $option)
                        <input type="hidden" name="options[]" value="{{ $option->id }}">
                    @endforeach

                    <div class="flex gap-4">
                        <button type="submit"
                                :disabled="submitting"
                                :class="submitting
                                    ? '{{ $isEvent ? 'from-violet-400 via-rose-400 to-orange-300 border-rose-300' : 'bg-blue-500' }} cursor-not-allowed opacity-70'
                                    : '{{ $isEvent ? 'reservation-flow-primary-btn--event' : 'reservation-flow-primary-btn--standard' }}'"
                                class="flex-1 px-6 py-3 text-white rounded-lg transition font-semibold text-center border focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span x-show="!submitting">予約を確定する</span>
                            <span x-show="submitting" x-cloak>確定中...</span>
                        </button>
                        <a href="{{ route('reservations.calendar', ['menu_id' => $menu->id]) }}"
                           :class="submitting ? 'pointer-events-none opacity-50' : ''"
                           class="px-6 py-3 rounded-lg border transition font-semibold {{ $isEvent ? 'reservation-flow-ghost-btn--event' : 'bg-gray-200 text-gray-700 border-gray-200 hover:bg-gray-300' }}">
                            戻る
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
