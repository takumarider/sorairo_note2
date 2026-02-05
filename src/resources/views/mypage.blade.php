<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            マイページ
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">あなたの予約</h3>
                <a href="{{ route('menus.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">新しく予約する</a>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-2xl p-4 sm:p-6">
                @if($reservations->count() > 0)
                    <div class="space-y-4">
                        @foreach($reservations as $reservation)
                            <div class="border rounded-xl p-4 sm:p-5 hover:shadow-md transition">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-500">予約番号 #{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="text-lg font-bold text-gray-900">{{ $reservation->menu->name }}</h4>
                                        <div class="space-y-1 text-gray-700">
                                            <p class="flex items-center text-sm">
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $reservation->slot->date->format('Y年m月d日') }}
                                            </p>
                                            <p class="flex items-center text-sm">
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $reservation->slot->start_time->format('H:i') }} - {{ $reservation->slot->end_time->format('H:i') }}
                                            </p>
                                            <p class="flex items-center text-sm">
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                ¥{{ number_format($reservation->menu->price) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex sm:flex-col gap-3 sm:items-end">
                                        <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" onsubmit="return confirm('この予約をキャンセルしますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                                                キャンセル
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-500">
                        未来の予約はありません。
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
