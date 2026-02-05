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
    <body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-cyan-100 text-slate-800 flex p-6 lg:p-10 items-center lg:justify-center flex-col">
        <header class="w-full lg:max-w-5xl max-w-[340px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex justify-end space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">ダッシュボード</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">ログイン</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">新規登録</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>   

        <main class="w-full max-w-[340px] lg:max-w-5xl flex-grow mx-auto px-4 py-10">
            <section class="w-full">
                <div class="rounded-3xl bg-white/70 backdrop-blur-lg border border-white/40 shadow-xl p-8 lg:p-12">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                        <div class="space-y-4 lg:max-w-2xl">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-800 text-sm font-semibold">sorairo_note</span>
                            <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight text-slate-900">
                                <br class="hidden lg:block" />ドライヘッドスパ
                                〜sorairo〜 
                            </h1>
                            <p class="text-lg lg:text-xl text-slate-600">
                                世の頑張る女性がほっと一息をつける場所。
                            </p>

                        </div>
                    </div>  
                </div>
                <div class="mt-10 grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 rounded-3xl bg-white/70 backdrop-blur-lg border border-white/40 shadow-xl p-6 lg:p-8">
                        <h2 class="text-2xl font-bold text-slate-900 mb-4">sorairo_note（ご予約）ご利用方法</h2>
                        <ol class="text-slate-700 grid gap-6 sm:grid-cols-2">
                            <li class="list-none rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex flex-col gap-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-12 h-12 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-lg">1</span>
                                    <div>
                                        <p class="font-semibold">ログイン／新規登録</p>
                                        <p class="text-sm text-slate-600">会員登録後にマイページへアクセスできます。初めての方もこちらからお手続きください。</p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                    @if (Route::has('login'))
                                        @auth
                                            <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-sky-500 to-cyan-500 text-white font-semibold shadow-lg shadow-sky-200 hover:translate-y-[-1px] transition-transform text-center">マイページへ</a>
                                        @else
                                            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-sky-500 to-cyan-500 text-white font-semibold shadow-lg shadow-sky-200 hover:translate-y-[-1px] transition-transform text-center">ログインする</a>
                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-white/80 border border-white/60 text-sky-800 font-semibold shadow hover:bg-white text-center">新規登録はこちら</a>
                                            @endif
                                        @endauth
                                    @endif
                                </div>
                            </li>
                            <li class="list-none rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex gap-3">
                                <span class="w-12 h-12 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-lg">2</span>
                                <div>
                                    <p class="font-semibold">メニューを選択</p>
                                    <p class="text-sm text-slate-600">ご希望のメニューを選択ください。所要時間と料金もご確認いただけます。</p>
                                </div>
                            </li>
                            <li class="list-none rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex gap-3">
                                <span class="w-12 h-12 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-lg">3</span>
                                <div>
                                    <p class="font-semibold">空き枠をチェック</p>
                                    <p class="text-sm text-slate-600">カレンダーから都合のよい時間を選び、すぐに確定。</p>
                                </div>
                            </li>
                            <li class="list-none rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex gap-3">
                                <span class="w-12 h-12 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-lg">4</span>
                                <div>
                                    <p class="font-semibold">サロンにお越しください</p>
                                    <p class="text-sm text-slate-600">タオルや着替えは不要。仕事帰りやお出かけ前にも。</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <div class="rounded-3xl bg-gradient-to-br from-sky-200/70 via-white to-cyan-200/70 backdrop-blur-lg border border-white/50 shadow-xl p-6 lg:p-8">
                        <h3 class="text-xl font-bold text-slate-900 mb-3">店舗情報</h3>
                        <div class="space-y-3 text-sm text-slate-700">
                            <p><span class="font-semibold text-slate-900">営業時間:</span> ⚫︎⚫︎:⚫︎⚫︎〜⚫︎⚫︎：⚫︎⚫︎</p>
                            <p><span class="font-semibold text-slate-900">定休日:</span> 不定期</p>
                            <p class="pt-2 text-slate-600"></p>
                            <a
                                href="https://www.instagram.com/06sorairo30"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 border border-sky-200 text-sky-800 font-semibold shadow hover:bg-white/90 transition"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Instagramで最新情報を見る
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>