<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $menu->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="aspect-[16/9] w-full overflow-hidden">
                    @if($menu->image_path)
                        <img src="{{ asset('storage/' . $menu->image_path) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400 text-xl">画像なし</span>
                        </div>
                    @endif
                </div>
                
                <div class="p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $menu->name }}</h1>
                    
                    <div class="flex items-center gap-6 mb-6">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-3xl font-bold text-blue-600">¥{{ number_format($menu->price) }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xl text-gray-700">{{ $menu->duration }}分</span>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">メニュー詳細</h2>
                        <p class="text-gray-700 leading-relaxed">{{ $menu->description }}</p>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="{{ route('slots.index', ['menu_id' => $menu->id]) }}" 
                           class="flex-1 text-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            日時を選択して予約
                        </a>
                        <a href="{{ route('menus.index') }}" 
                           class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                            戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
