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
    @php
        $themeBackgroundClass = [
            'sky' => 'bg-gradient-to-br from-sky-50 via-white to-cyan-100',
            'mint' => 'bg-gradient-to-br from-emerald-50 via-white to-teal-100',
            'sand' => 'bg-gradient-to-br from-amber-50 via-white to-orange-100',
        ][$settings->welcome_theme_background ?? ''] ?? 'bg-gradient-to-br from-sky-50 via-white to-cyan-100';

        $accentClasses = [
            'sky' => [
                'badge' => 'bg-sky-100 text-sky-800',
                'button' => 'border-sky-200 text-sky-800',
            ],
            'emerald' => [
                'badge' => 'bg-emerald-100 text-emerald-800',
                'button' => 'border-emerald-200 text-emerald-800',
            ],
            'rose' => [
                'badge' => 'bg-rose-100 text-rose-800',
                'button' => 'border-rose-200 text-rose-800',
            ],
        ][$settings->welcome_theme_accent ?? ''] ?? [
            'badge' => 'bg-sky-100 text-sky-800',
            'button' => 'border-sky-200 text-sky-800',
        ];

        $heroAlignClass = [
            'left' => 'text-left',
            'center' => 'text-center',
        ][$settings->welcome_hero_text_align ?? ''] ?? 'text-left';

        $heroTitleSizeClass = [
            'md' => 'text-3xl lg:text-4xl',
            'lg' => 'text-4xl lg:text-5xl',
            'xl' => 'text-5xl lg:text-6xl',
        ][$settings->welcome_hero_title_size ?? ''] ?? 'text-4xl lg:text-5xl';

        $heroTitleColorClass = [
            'slate' => 'text-slate-900',
            'sky' => 'text-sky-900',
            'emerald' => 'text-emerald-900',
        ][$settings->welcome_hero_title_color ?? ''] ?? 'text-slate-900';

        $heroSubtitleSizeClass = [
            'sm' => 'text-lg lg:text-xl',
            'md' => 'text-xl lg:text-2xl',
            'lg' => 'text-2xl lg:text-3xl',
        ][$settings->welcome_hero_subtitle_size ?? ''] ?? 'text-xl lg:text-2xl';

        $heroSubtitleColorClass = [
            'sky' => 'text-sky-800',
            'emerald' => 'text-emerald-800',
            'rose' => 'text-rose-800',
        ][$settings->welcome_hero_subtitle_color ?? ''] ?? 'text-sky-800';

        $heroLeadSizeClass = [
            'sm' => 'text-base lg:text-lg',
            'md' => 'text-lg lg:text-xl',
            'lg' => 'text-xl lg:text-2xl',
        ][$settings->welcome_hero_lead_size ?? ''] ?? 'text-lg lg:text-xl';

        $heroLeadColorClass = [
            'slate' => 'text-slate-600',
            'sky' => 'text-sky-700',
            'emerald' => 'text-emerald-700',
        ][$settings->welcome_hero_lead_color ?? ''] ?? 'text-slate-600';

        $heroLeadMode = in_array($settings->welcome_hero_lead_paragraph_mode, ['line', 'paragraph'], true)
            ? $settings->welcome_hero_lead_paragraph_mode
            : 'line';
        $shopParagraphMode = in_array($settings->welcome_shop_paragraph_mode, ['line', 'paragraph'], true)
            ? $settings->welcome_shop_paragraph_mode
            : 'line';

        $shopTitleSizeClass = [
            'sm' => 'text-lg',
            'md' => 'text-xl',
            'lg' => 'text-2xl',
        ][$settings->welcome_shop_title_size ?? ''] ?? 'text-xl';
        $shopTitleColorClass = [
            'slate' => 'text-slate-900',
            'sky' => 'text-sky-900',
            'emerald' => 'text-emerald-900',
        ][$settings->welcome_shop_title_color ?? ''] ?? 'text-slate-900';
        $shopBodySizeClass = [
            'sm' => 'text-xs',
            'md' => 'text-sm',
            'lg' => 'text-base',
        ][$settings->welcome_shop_body_size ?? ''] ?? 'text-sm';
        $shopBodyColorClass = [
            'slate' => 'text-slate-700',
            'sky' => 'text-sky-800',
            'emerald' => 'text-emerald-800',
        ][$settings->welcome_shop_body_color ?? ''] ?? 'text-slate-700';

        $toParagraphs = static function (?string $text): array {
            if (! filled($text)) {
                return [];
            }

            $normalized = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
            $parts = preg_split('/\n{2,}/', $normalized) ?: [];

            return array_values(array_filter($parts, static fn (string $part): bool => filled(trim($part))));
        };
    @endphp
    <body class="min-h-screen {{ $themeBackgroundClass }} text-slate-800 flex p-6 lg:p-10 items-center lg:justify-center flex-col">
        <header class="w-full lg:max-w-5xl max-w-[340px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex justify-end items-center gap-4 flex-wrap">
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">ダッシュボード</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">ログイン</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-semibold text-slate-800 hover:text-sky-700 transition">新規登録</a>
                        @endif
                    @endauth
                    <a
                        href="{{ $settings->welcome_instagram_url ?: 'https://www.instagram.com/06sorairo30' }}"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 border {{ $accentClasses['button'] }} font-semibold shadow hover:bg-white transition"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Instagram
                    </a>
                </nav>
            @endif
        </header>   

        <main class="w-full max-w-[340px] lg:max-w-5xl flex-grow mx-auto px-4 py-10">
            <section class="w-full">
                <div class="rounded-3xl bg-white/70 backdrop-blur-lg border border-white/40 shadow-xl p-8 lg:p-12">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                        <div class="space-y-4 lg:max-w-2xl {{ $heroAlignClass }}">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $accentClasses['badge'] }} text-sm font-semibold">{{ $settings->welcome_badge ?: 'sorairo_note' }}</span>
                            <h1 class="{{ $heroTitleSizeClass }} font-extrabold leading-tight {{ $heroTitleColorClass }}">{{ $settings->welcome_title ?: 'ドライヘッドスパ 〜sorairo〜' }}</h1>
                            @if (filled($settings->welcome_subtitle))
                                <p class="{{ $heroSubtitleSizeClass }} font-semibold {{ $heroSubtitleColorClass }}">{{ $settings->welcome_subtitle }}</p>
                            @endif
                            @php
                                $heroLeadText = $settings->welcome_lead ?: '世の頑張る女性がほっと一息をつける場所。';
                            @endphp
                            @if ($heroLeadMode === 'paragraph')
                                @foreach ($toParagraphs($heroLeadText) as $paragraph)
                                    <p class="{{ $heroLeadSizeClass }} {{ $heroLeadColorClass }} whitespace-pre-line">{{ $paragraph }}</p>
                                @endforeach
                            @else
                                <p class="{{ $heroLeadSizeClass }} {{ $heroLeadColorClass }} whitespace-pre-line">{{ $heroLeadText }}</p>
                            @endif
                        </div>
                        @if (filled($settings->welcome_main_image_path))
                            <div class="lg:w-[320px] w-full">
                                <div class="w-full overflow-hidden rounded-2xl bg-slate-100 p-2 shadow-lg">
                                    <img
                                        src="{{ Storage::url($settings->welcome_main_image_path) }}"
                                        alt="店舗イメージ"
                                        class="h-auto max-h-[420px] w-full object-contain"
                                    >
                                </div>
                            </div>
                        @endif
                    </div>  
                </div>
                <div class="mt-10 grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 rounded-3xl bg-white/70 backdrop-blur-lg border border-white/40 shadow-xl p-6 lg:p-8">
                        <h2 class="text-2xl font-bold text-slate-900 mb-4">お店のご案内</h2>
                        @if ($bodyBlocks->isNotEmpty())
                            @php $blockCount = $bodyBlocks->count(); @endphp
                            @if ($blockCount === 1)
                                {{-- 1件のみはシンプルカード表示 --}}
                                @foreach ($bodyBlocks as $block)
                                    <article class="rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex flex-col gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-sm">1</span>
                                            <div>
                                                @php
                                                    $blockTitleSizeClass = [
                                                        'sm' => 'text-sm',
                                                        'md' => 'text-base',
                                                        'lg' => 'text-lg',
                                                    ][data_get($block, 'title_size')] ?? 'text-base';
                                                    $blockTitleColorClass = [
                                                        'slate' => 'text-slate-900',
                                                        'sky' => 'text-sky-900',
                                                        'emerald' => 'text-emerald-900',
                                                    ][data_get($block, 'title_color')] ?? 'text-slate-900';
                                                    $blockTextSizeClass = [
                                                        'sm' => 'text-xs',
                                                        'md' => 'text-sm',
                                                        'lg' => 'text-base',
                                                    ][data_get($block, 'text_size')] ?? 'text-sm';
                                                    $blockTextColorClass = [
                                                        'slate' => 'text-slate-600',
                                                        'sky' => 'text-sky-700',
                                                        'emerald' => 'text-emerald-700',
                                                    ][data_get($block, 'text_color')] ?? 'text-slate-600';
                                                    $blockTextAlignClass = [
                                                        'left' => 'text-left',
                                                        'center' => 'text-center',
                                                    ][data_get($block, 'text_align')] ?? 'text-left';
                                                    $blockMode = data_get($block, 'paragraph_mode');
                                                    if (! in_array($blockMode, ['line', 'paragraph'], true)) {
                                                        $blockMode = 'line';
                                                    }
                                                @endphp
                                                <h3 class="font-semibold {{ $blockTitleSizeClass }} {{ $blockTitleColorClass }}">{{ data_get($block, 'title') }}</h3>
                                                @if ($blockMode === 'paragraph')
                                                    @foreach ($toParagraphs((string) data_get($block, 'text')) as $paragraph)
                                                        <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $paragraph }}</p>
                                                    @endforeach
                                                @else
                                                    <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ data_get($block, 'text') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if (filled(data_get($block, 'image_path')))
                                            <div class="w-full overflow-hidden rounded-xl bg-slate-100 p-2">
                                                <img src="{{ Storage::url(data_get($block, 'image_path') ?? '') }}" alt="セクション画像" class="h-auto max-h-[360px] w-full object-contain">
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            @else
                                {{-- 複数件はスライドカルーセル --}}
                                <div
                                    x-data="{
                                        current: 0,
                                        total: {{ $blockCount }},
                                        sliding: false,
                                        direction: 'right',
                                        prev() {
                                            if (this.sliding) return;
                                            this.direction = 'left';
                                            this.sliding = true;
                                            setTimeout(() => { this.current = (this.current - 1 + this.total) % this.total; this.sliding = false; }, 350);
                                        },
                                        next() {
                                            if (this.sliding) return;
                                            this.direction = 'right';
                                            this.sliding = true;
                                            setTimeout(() => { this.current = (this.current + 1) % this.total; this.sliding = false; }, 350);
                                        }
                                    }"
                                    class="relative"
                                >
                                    <style>
                                        .slide-enter-right { animation: slideInRight .35s ease both; }
                                        .slide-enter-left  { animation: slideInLeft  .35s ease both; }
                                        @keyframes slideInRight {
                                            from { opacity: 0; transform: translateX(40px); }
                                            to   { opacity: 1; transform: translateX(0); }
                                        }
                                        @keyframes slideInLeft {
                                            from { opacity: 0; transform: translateX(-40px); }
                                            to   { opacity: 1; transform: translateX(0); }
                                        }
                                    </style>

                                    {{-- スライド本体 --}}
                                    @foreach ($bodyBlocks as $index => $block)
                                        <article
                                            x-show="current === {{ $index }}"
                                            x-transition:enter.duration.350ms
                                            :class="direction === 'right' ? 'slide-enter-right' : 'slide-enter-left'"
                                            class="rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5 flex flex-col gap-4"
                                            style="{{ $index !== 0 ? 'display:none;' : '' }}"
                                        >
                                            <div class="flex items-start gap-3">
                                                <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-sm">{{ $index + 1 }}</span>
                                                <div>
                                                    @php
                                                        $blockTitleSizeClass = [
                                                            'sm' => 'text-sm',
                                                            'md' => 'text-base',
                                                            'lg' => 'text-lg',
                                                        ][data_get($block, 'title_size')] ?? 'text-base';
                                                        $blockTitleColorClass = [
                                                            'slate' => 'text-slate-900',
                                                            'sky' => 'text-sky-900',
                                                            'emerald' => 'text-emerald-900',
                                                        ][data_get($block, 'title_color')] ?? 'text-slate-900';
                                                        $blockTextSizeClass = [
                                                            'sm' => 'text-xs',
                                                            'md' => 'text-sm',
                                                            'lg' => 'text-base',
                                                        ][data_get($block, 'text_size')] ?? 'text-sm';
                                                        $blockTextColorClass = [
                                                            'slate' => 'text-slate-600',
                                                            'sky' => 'text-sky-700',
                                                            'emerald' => 'text-emerald-700',
                                                        ][data_get($block, 'text_color')] ?? 'text-slate-600';
                                                        $blockTextAlignClass = [
                                                            'left' => 'text-left',
                                                            'center' => 'text-center',
                                                        ][data_get($block, 'text_align')] ?? 'text-left';
                                                        $blockMode = data_get($block, 'paragraph_mode');
                                                        if (! in_array($blockMode, ['line', 'paragraph'], true)) {
                                                            $blockMode = 'line';
                                                        }
                                                    @endphp
                                                    <h3 class="font-semibold {{ $blockTitleSizeClass }} {{ $blockTitleColorClass }}">{{ data_get($block, 'title') }}</h3>
                                                    @if ($blockMode === 'paragraph')
                                                        @foreach ($toParagraphs((string) data_get($block, 'text')) as $paragraph)
                                                            <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $paragraph }}</p>
                                                        @endforeach
                                                    @else
                                                        <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ data_get($block, 'text') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if (filled(data_get($block, 'image_path')))
                                                <div class="w-full overflow-hidden rounded-xl bg-slate-100 p-2">
                                                    <img src="{{ Storage::url(data_get($block, 'image_path') ?? '') }}" alt="セクション画像" class="h-auto max-h-[360px] w-full object-contain">
                                                </div>
                                            @endif
                                        </article>
                                    @endforeach

                                    {{-- ナビゲーション --}}
                                    <div class="mt-4 flex items-center justify-between">
                                        <button
                                            @click="prev()"
                                            class="flex items-center gap-1 px-4 py-2 rounded-full bg-white/80 border border-sky-200 text-sky-700 font-semibold text-sm shadow hover:bg-sky-50 transition disabled:opacity-40"
                                            :disabled="sliding"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                            前へ
                                        </button>

                                        {{-- インジケーター --}}
                                        <div class="flex gap-2">
                                            @foreach ($bodyBlocks as $index => $block)
                                                <button
                                                    @click="current = {{ $index }}"
                                                    :class="current === {{ $index }} ? 'bg-sky-500 w-5' : 'bg-sky-200 w-2.5'"
                                                    class="h-2.5 rounded-full transition-all duration-300"
                                                ></button>
                                            @endforeach
                                        </div>

                                        <button
                                            @click="next()"
                                            class="flex items-center gap-1 px-4 py-2 rounded-full bg-white/80 border border-sky-200 text-sky-700 font-semibold text-sm shadow hover:bg-sky-50 transition disabled:opacity-40"
                                            :disabled="sliding"
                                        >
                                            次へ
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>

                                    {{-- 件数表示 --}}
                                    <p class="mt-2 text-center text-xs text-slate-400">
                                        <span x-text="current + 1">1</span> / {{ $blockCount }}
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="rounded-2xl border border-white/60 bg-white/80 shadow-sm p-5">
                                <p class="text-slate-700">管理画面から本文セクションを追加すると、ここに表示されます。</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-3xl bg-gradient-to-br from-sky-200/70 via-white to-cyan-200/70 backdrop-blur-lg border border-white/50 shadow-xl p-6 lg:p-8">
                        <h3 class="{{ $shopTitleSizeClass }} font-bold {{ $shopTitleColorClass }} mb-3">{{ $settings->welcome_shop_title ?: '店舗情報' }}</h3>
                        <div class="space-y-3 {{ $shopBodySizeClass }} {{ $shopBodyColorClass }}">
                            @if (filled($settings->welcome_shop_description))
                                @if ($shopParagraphMode === 'paragraph')
                                    @foreach ($toParagraphs($settings->welcome_shop_description) as $paragraph)
                                        <p class="whitespace-pre-line">{{ $paragraph }}</p>
                                    @endforeach
                                @else
                                    <p class="whitespace-pre-line">{{ $settings->welcome_shop_description }}</p>
                                @endif
                            @endif
                            <p class="whitespace-pre-line"><span class="font-semibold text-slate-900">営業時間:</span> {{ $settings->welcome_business_hours ?: '管理画面から設定してください' }}</p>
                            <p><span class="font-semibold text-slate-900">定休日:</span> {{ $settings->welcome_regular_holiday ?: '管理画面から設定してください' }}</p>
                            <p><span class="font-semibold text-slate-900">お問い合わせ:</span> {{ $settings->welcome_contact_number ?: '管理画面から設定してください' }}</p>
                            @if (filled($settings->welcome_business_note))
                                @if ($shopParagraphMode === 'paragraph')
                                    @foreach ($toParagraphs($settings->welcome_business_note) as $paragraph)
                                        <p class="pt-1 whitespace-pre-line">{{ $paragraph }}</p>
                                    @endforeach
                                @else
                                    <p class="pt-1 whitespace-pre-line">{{ $settings->welcome_business_note }}</p>
                                @endif
                            @endif
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