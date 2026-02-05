<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('予約完了') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-600 text-white p-6 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-3xl font-bold">予約が完了しました！</h2>
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
                            <p class="text-xl font-bold text-gray-900">{{ $reservation->menu->name }}</p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="text-sm text-gray-500 mb-1">日時</h3>
                            <p class="text-xl font-bold text-gray-900">
                                {{ $reservation->slot->date->isoFormat('Y年M月D日(ddd)') }}
                                <span class="ml-2">{{ $reservation->slot->start_time->format('H:i') }} - {{ $reservation->slot->end_time->format('H:i') }}</span>
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
                        <a href="{{ route('dashboard') }}" 
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
