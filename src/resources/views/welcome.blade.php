<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Vite を使用 -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Sorairo Note is a personal note-taking application designed to help you organize your thoughts and ideas efficiently.">       
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex justify-end space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-medium text-[#1b1b18] dark:text-[#f3f3f0] hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-[#1b1b18] dark:text-[#f3f3f0] hover:underline">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-medium text-[#1b1b18] dark:text-[#f3f3f0] hover:underline">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>   

        <main class="w-full max-w-[335px] lg:max-w-4xl flex-grow mx-auto px-4 py-12">
            <section class="text-center">
                <!-- タイトル -->
                <h1 class="text-4xl lg:text-5xl font-extrabold mb-4 text-gray-900">
                    Welcome to Sorairo Note
                </h1>

                <!-- サブテキスト -->
                <p class="text-lg lg:text-xl text-gray-600 mb-8">
                    Your personal note-taking application.
                </p>

                <!-- 🎯 動作確認セクション（追加） -->
                <div class="mb-12 p-6 bg-white rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">🔧 動作確認パネル</h2>
                    
                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                        <!-- Vite 確認 -->
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-lg test-vite-working">
                            <div class="text-3xl mb-2">⚡</div>
                            <h3 class="font-bold">Vite</h3>
                            <p class="text-sm">アニメーション動作中</p>
                        </div>

                        <!-- Tailwind CSS 確認 -->
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-lg hover:scale-105 transition-transform duration-300">
                            <div class="text-3xl mb-2">🎨</div>
                            <h3 class="font-bold">Tailwind CSS</h3>
                            <p class="text-sm">ホバーで拡大</p>
                        </div>

                        <!-- jQuery 確認 -->
                        <div class="p-4 bg-gradient-to-br from-green-500 to-green-700 text-white rounded-lg test-jquery cursor-pointer">
                            <div class="text-3xl mb-2">💚</div>
                            <h3 class="font-bold">jQuery</h3>
                            <p class="text-sm">クリックしてテスト</p>
                        </div>
                    </div>

                    <!-- 確認方法の説明 -->
                    <div class="text-left bg-gray-50 p-4 rounded-lg text-sm">
                        <p class="font-semibold mb-2">✅ 確認方法:</p>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            <li><strong>Vite:</strong> 紫のカードがパルスアニメーション</li>
                            <li><strong>Tailwind CSS:</strong> 青のカードにホバーで拡大</li>
                            <li><strong>jQuery:</strong> 緑のカード��クリックでアラート表示</li>
                            <li><strong>コンソール:</strong> F12 → Console で確認メッセージ</li>
                        </ul>
                    </div>
                </div>

                <!-- ログイン / ダッシュボード ボタン（そのまま） -->
                @if (Route::has('login'))
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold shadow hover:bg-green-700 transition">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" 
                                   class="px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold shadow hover:bg-gray-700 transition">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </section>
        </main>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>