<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('メニュー選択') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">メニューを選択してください</h2>
            
            @if($menus->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($menus as $menu)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <div class="aspect-[4/3] w-full overflow-hidden">
                                @if($menu->image_path)
                                    <img src="{{ asset('storage/' . $menu->image_path) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400">画像なし</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $menu->name }}</h3>
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $menu->description }}</p>
                                
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-2xl font-bold text-blue-600">¥{{ number_format($menu->price) }}</span>
                                    <span class="text-gray-500">{{ $menu->duration }}分</span>
                                </div>
                                
                                <a href="{{ route('slots.index', ['menu_id' => $menu->id]) }}" 
                                   class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    日時を選択
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <p class="text-gray-500 text-lg">現在、予約可能なメニューはありません。</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
