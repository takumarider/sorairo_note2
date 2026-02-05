<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('予約内容確認') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 text-white p-6">
                    <h2 class="text-2xl font-bold">予約内容をご確認ください</h2>
                </div>
                
                <div class="p-8">
                    <div class="space-y-6 mb-8">
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">メニュー</h3>
                            <p class="text-xl font-bold text-gray-900">{{ $slot->menu->name }}</p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">日時</h3>
                            <p class="text-xl font-bold text-gray-900">
                                {{ $slot->date->isoFormat('Y年M月D日(ddd)') }}
                                <span class="ml-2">{{ $slot->start_time->format('H:i') }} - {{ $slot->end_time->format('H:i') }}</span>
                            </p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">料金</h3>
                            <p class="text-2xl font-bold text-blue-600">¥{{ number_format($slot->menu->price) }}</p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">所要時間</h3>
                            <p class="text-xl font-bold text-gray-900">{{ $slot->menu->duration }}分</p>
                        </div>
                        
                        @if($slot->menu->description)
                        <div class="pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">メニュー詳細</h3>
                            <p class="text-gray-700">{{ $slot->menu->description }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <form method="POST" action="{{ route('reservations.store') }}">
                        @csrf
                        <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                        
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold text-lg">
                                予約を確定する
                            </button>
                            <a href="{{ route('slots.index', ['menu_id' => $slot->menu_id]) }}" 
                               class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                                戻る
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
