<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $menu->name }} - 時間選択
        </h2>
    </x-slot>

<div class="min-h-screen bg-gray-50">
    <!-- ヘッダー -->
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm">
        <div class="px-4 py-4 sm:px-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $menu->name }}</h1>
                    <p class="text-lg sm:text-xl font-semibold text-blue-600 mt-1">¥{{ number_format($menu->price) }}</p>
                </div>
                <a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- メインコンテンツ -->
    <div class="px-4 py-6 sm:px-6">
        <!-- 週間ナビゲーション -->
        <div class="mb-6">
            <div class="flex items-center justify-between gap-2 mb-4">
                <a href="{{ route('slots.index', ['menu_id' => $menu->id, 'week_start' => $prevWeek->toDateString()]) }}"
                   class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm font-semibold text-gray-700 text-center">
                    ← 前
                </a>
                <div class="flex-2 text-center">
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">
                        {{ $weekStart->format('m/d') }} ～ {{ $weekEnd->format('m/d') }}
                    </h2>
                </div>
                <a href="{{ route('slots.index', ['menu_id' => $menu->id, 'week_start' => $nextWeek->toDateString()]) }}"
                   class="flex-1 px-3 py-2 bg-blue-500 hover:bg-blue-600 rounded text-sm font-semibold text-white text-center">
                    次 →
                </a>
            </div>

            <!-- 日付タブ (モバイル: スクロール可能) -->
            <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
                @foreach($weekDays as $day)
                    <button type="button" 
                            data-date="{{ $day['date']->format('Y-m-d') }}"
                            class="date-tab flex-shrink-0 px-4 py-3 rounded-lg font-semibold text-center whitespace-nowrap transition-colors
                                   {{ $day['date']->isPast() 
                                       ? 'bg-gray-200 text-gray-500 cursor-not-allowed' 
                                       : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-blue-500' }}">
                        <div class="text-sm font-bold">{{ $day['date']->format('m/d') }}</div>
                        <div class="text-xs text-gray-600">{{ $day['date']->format('(D)') }}</div>
                        <div class="text-xs mt-1">
                            @if($day['slots']->where('is_reserved', false)->count() > 0)
                                <span class="text-blue-600">{{ $day['slots']->where('is_reserved', false)->count() }}件</span>
                            @else
                                <span class="text-gray-400">なし</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 選択した日付のスロット表示 -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-4">選択可能な時間</h3>
            <div id="slots-container" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <!-- JavaScriptで動的に生成 -->
            </div>
        </div>

        <!-- 選択結果表示 -->
        <div class="bg-blue-50 rounded-lg p-4 sm:p-6 mb-6 border border-blue-200">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">選択した日時</h3>
            <div id="selected-slot-info" class="text-gray-600 text-sm">
                まだ選択されていません
            </div>
            <div id="selected-slot-display" class="hidden mt-3">
                <div class="bg-white rounded p-3">
                    <p class="text-lg sm:text-xl font-bold text-blue-600" id="selected-datetime"></p>
                </div>
            </div>
        </div>

        <!-- アクション（固定） -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-xl">
            <button id="confirm-button" type="submit"
                    class="w-full px-6 py-4 rounded-xl font-bold text-lg text-white
                           bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                           transition duration-200 ease-out
                           shadow-lg hover:shadow-xl transform hover:-translate-y-0.5
                           ring-2 ring-blue-200
                           disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none disabled:ring-0 disabled:translate-y-0"
                    disabled>
                決定して予約確認へ進む
            </button>
            <p class="text-xs text-gray-500 text-center mt-2">日時を選択してください</p>
        </div>

        <!-- ボタン用スペーサー -->
        <div class="h-24"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedSlot = null;
    let selectedDate = null;
    
    // サーバーから全スロット情報を取得
    const allSlots = {!! json_encode($weekDays->mapWithKeys(fn($day) => [
        $day['date']->format('Y-m-d') => $day['slots']->map(fn($slot) => [
            'id' => $slot->id,
            'time' => $slot->start_time->format('H:i'),
            'is_reserved' => $slot->is_reserved,
            'date' => $day['date']->format('Y-m-d')
        ])
    ])) !!};

    // 日付タブのクリック処理
    document.querySelectorAll('.date-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            if (this.closest('button').classList.contains('cursor-not-allowed')) return;

            const date = this.dataset.date;
            
            // アクティブ状態を更新
            document.querySelectorAll('.date-tab').forEach(t => {
                t.classList.remove('bg-blue-500', 'border-blue-500', 'text-white');
                t.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
            });
            this.classList.remove('bg-white', 'border-gray-200', 'text-gray-700');
            this.classList.add('bg-blue-500', 'border-blue-500', 'text-white');

            selectedDate = date;
            renderSlots(date);
            
            // 選択をリセット
            selectedSlot = null;
            updateSelectedDisplay();
        });
    });

    // スロットを動的にレンダリング
    function renderSlots(date) {
        const container = document.getElementById('slots-container');
        const slots = allSlots[date] || [];

        if (slots.length === 0) {
            container.innerHTML = '<p class="col-span-2 sm:col-span-3 text-gray-500 text-center py-8">この日は予約枠がありません</p>';
            return;
        }

        container.innerHTML = slots.map(slot => `
            <button type="button"
                    data-slot-id="${slot.id}"
                    data-slot-time="${slot.time}"
                    data-slot-date="${slot.date}"
                    class="slot-button px-4 py-3 rounded-lg font-semibold transition-colors text-center
                           ${slot.is_reserved 
                               ? 'bg-gray-200 text-gray-500 cursor-not-allowed' 
                               : 'bg-green-100 text-green-800 hover:bg-green-200 border-2 border-green-300'}
                           ${slot.is_reserved ? 'disabled' : ''}">
                <div class="text-base sm:text-lg font-bold">${slot.time}</div>
                <div class="text-xs">${slot.is_reserved ? '✖︎ 予約済み' : '⚫︎ 可能'}</div>
            </button>
        `).join('');

        // スロットボタンのイベント設定
        document.querySelectorAll('.slot-button:not(:disabled)').forEach(button => {
            button.addEventListener('click', function() {
                selectSlot(this);
            });
        });
    }

    // スロット選択処理
    function selectSlot(button) {
        // 前の選択をリセット
        document.querySelectorAll('.slot-button:not(:disabled)').forEach(b => {
            b.classList.remove('bg-blue-400', 'text-white');
            b.classList.add('bg-green-100', 'text-green-800');
        });

        // 新しい選択をハイライト
        button.classList.remove('bg-green-100', 'text-green-800');
        button.classList.add('bg-blue-500', 'text-white');

        selectedSlot = {
            id: button.dataset.slotId,
            time: button.dataset.slotTime,
            date: button.dataset.slotDate
        };

        updateSelectedDisplay();
    }

    // 選択結果を更新
    function updateSelectedDisplay() {
        const infoDiv = document.getElementById('selected-slot-info');
        const displayDiv = document.getElementById('selected-slot-display');
        const datetimeDiv = document.getElementById('selected-datetime');
        const confirmBtn = document.getElementById('confirm-button');

        if (!selectedSlot) {
            infoDiv.textContent = 'まだ選択されていません';
            displayDiv.classList.add('hidden');
            confirmBtn.disabled = true;
            confirmBtn.textContent = '予約確認へ進む';
        } else {
            infoDiv.textContent = '';
            displayDiv.classList.remove('hidden');
            
            // 日付をフォーマット
            const dateObj = new Date(selectedSlot.date + 'T00:00:00');
            const dateStr = dateObj.toLocaleDateString('ja-JP', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                weekday: 'short'
            });
            
            datetimeDiv.textContent = `${dateStr} ${selectedSlot.time}`;
            confirmBtn.disabled = false;
            confirmBtn.textContent = '✓ 予約確認へ進む';
        }
    }

    // 確認ボタンのクリック処理
    document.getElementById('confirm-button').addEventListener('click', function() {
        if (selectedSlot) {
            window.location.href = "{{ route('reservations.confirm') }}?slot_id=" + selectedSlot.id;
        }
    });

    // 初期表示：最初の利用可能な日付を選択
    const firstAvailableDate = document.querySelector('.date-tab:not(.cursor-not-allowed)');
    if (firstAvailableDate) {
        firstAvailableDate.click();
    }
});
</script>
</div>
</x-app-layout>
