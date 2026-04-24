<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('予約完了') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden reservation-complete-card">
                <div class="reservation-complete-hero {{ $reservation->menu->is_event ? 'reservation-complete-hero--event' : 'reservation-complete-hero--standard' }}">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-3xl font-bold">{{ $reservation->menu->is_event ? 'イベント参加予約が完了しました！' : '予約が完了しました！' }}</h2>
                    <span class="reservation-complete-chip {{ $reservation->menu->is_event ? 'reservation-complete-chip--event' : 'reservation-complete-chip--standard' }}">
                        {{ $reservation->menu->is_event ? 'EVENT' : 'RESERVATION' }}
                    </span>
                </div>
                
                <div class="p-8">
                    <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mb-6">
                        <p class="text-sm text-blue-800">
                            予約番号: <span class="font-bold">#{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </p>
                    </div>
                    
                    <div class="space-y-6 mb-8">
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">メニュー</h3>
                            <p class="text-xl font-bold text-gray-900">
                                {{ $reservation->menu->name }}
                                <span class="reservation-complete-inline-chip {{ $reservation->menu->is_event ? 'reservation-complete-inline-chip--event' : 'reservation-complete-inline-chip--standard' }}">
                                    {{ $reservation->menu->is_event ? 'EVENT' : 'BOOKED' }}
                                </span>
                            </p>
                        </div>

                        @if(!$reservation->menu->is_event && $reservation->options && $reservation->options->isNotEmpty())
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-2">オプション</h3>
                            <div class="space-y-1">
                                @foreach($reservation->options as $option)
                                    <p class="text-gray-700">{{ $option->name }}</p>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">{{ $reservation->menu->is_event ? 'イベント開催日時' : '日時' }}</h3>
                            <p class="text-xl font-bold text-gray-900">
                                {{ $reservation->date->isoFormat('Y年M月D日(dddd)') }}
                                <span class="ml-2">{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</span>
                            </p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">料金</h3>
                            <p class="text-2xl font-bold text-blue-600">¥{{ number_format($reservation->menu->price) }}</p>
                        </div>
                        
                        <div class="pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">予約日時</h3>
                            <p class="text-gray-700">{{ $reservation->created_at->isoFormat('Y年M月D日 HH:mm') }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <p class="text-sm text-yellow-800">
                            <strong>注意事項</strong><br>
                            予約の変更・キャンセルはマイページから行うことができます。
                        </p>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="{{ route('mypage') }}" 
                           class="flex-1 text-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold">
                            マイページへ
                        </a>
                        <a href="{{ route('menus.index') }}" 
                           class="flex-1 text-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                            メニュー一覧へ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
