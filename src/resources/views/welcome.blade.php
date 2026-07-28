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
        $themeBackgroundClass = \App\Support\WelcomeStyleHelper::themeBackgroundClass($settings->welcome_theme_background);
        $accentClasses = \App\Support\WelcomeStyleHelper::accentClasses($settings->welcome_theme_accent);
        $heroAlignClass = \App\Support\WelcomeStyleHelper::heroAlignClass($settings->welcome_hero_text_align);
        $heroTitleSizeClass = \App\Support\WelcomeStyleHelper::heroTitleSizeClass($settings->welcome_hero_title_size);
        $heroTitleColorClass = \App\Support\WelcomeStyleHelper::heroTitleColorClass($settings->welcome_hero_title_color);
        $heroSubtitleSizeClass = \App\Support\WelcomeStyleHelper::heroSubtitleSizeClass($settings->welcome_hero_subtitle_size);
        $heroSubtitleColorClass = \App\Support\WelcomeStyleHelper::heroSubtitleColorClass($settings->welcome_hero_subtitle_color);
        $heroLeadSizeClass = \App\Support\WelcomeStyleHelper::heroLeadSizeClass($settings->welcome_hero_lead_size);
        $heroLeadColorClass = \App\Support\WelcomeStyleHelper::heroLeadColorClass($settings->welcome_hero_lead_color);
        $heroLeadMode = \App\Support\WelcomeStyleHelper::paragraphMode($settings->welcome_hero_lead_paragraph_mode);
        $shopParagraphMode = \App\Support\WelcomeStyleHelper::paragraphMode($settings->welcome_shop_paragraph_mode);
        $shopTitleSizeClass = \App\Support\WelcomeStyleHelper::shopTitleSizeClass($settings->welcome_shop_title_size);
        $shopTitleColorClass = \App\Support\WelcomeStyleHelper::shopTitleColorClass($settings->welcome_shop_title_color);
        $shopBodySizeClass = \App\Support\WelcomeStyleHelper::shopBodySizeClass($settings->welcome_shop_body_size);
        $shopBodyColorClass = \App\Support\WelcomeStyleHelper::shopBodyColorClass($settings->welcome_shop_body_color);
        $cardPaddingHeroClass = \App\Support\WelcomeStyleHelper::cardPaddingHeroClass($settings->welcome_card_padding);
        $cardPaddingShopClass = \App\Support\WelcomeStyleHelper::cardPaddingShopClass($settings->welcome_card_padding);
        $cardPaddingBlockClass = \App\Support\WelcomeStyleHelper::cardPaddingBlockClass($settings->welcome_card_padding);
        $cardRadiusClass = \App\Support\WelcomeStyleHelper::cardRadiusClass($settings->welcome_card_radius);
        $cardShadowClass = \App\Support\WelcomeStyleHelper::cardShadowClass($settings->welcome_card_shadow);
        $fontStyleClass = \App\Support\WelcomeStyleHelper::fontStyleClass($settings->welcome_font_style);

        $toParagraphs = static function (?string $text): array {
            if (! filled($text)) {
                return [];
            }

            $normalized = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
            $parts = preg_split('/\n{2,}/', $normalized) ?: [];

            return array_values(array_filter($parts, static fn (string $part): bool => filled(trim($part))));
        };
    @endphp
    <body class="min-h-screen {{ $themeBackgroundClass }} {{ $fontStyleClass }} text-slate-800 flex p-6 lg:p-10 items-center lg:justify-center flex-col">
        <header class="w-full lg:max-w-5xl max-w-[340px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                @php
                    $headerButtonBase = 'shrink-0 inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-xs sm:px-4 sm:py-2 sm:text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
                @endphp
                <nav class="flex justify-end items-center gap-2 whitespace-nowrap overflow-x-auto">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="{{ $headerButtonBase }} bg-gradient-to-r from-slate-500 to-slate-700 text-white shadow-sm hover:from-slate-600 hover:to-slate-800 focus-visible:ring-slate-300"
                        >
                            ダッシュボード
                        </a>
                    @else
                        <a
                            href="{{ route('menus.index') }}"
                            class="{{ $headerButtonBase }} bg-gradient-to-r from-sky-500 to-cyan-500 text-white shadow-sm hover:from-sky-600 hover:to-cyan-600 focus-visible:ring-sky-300"
                        >
                            メニューを見る
                        </a>
                        <a
                            href="{{ route('login') }}"
                            class="{{ $headerButtonBase }} bg-gradient-to-r from-sky-500 to-cyan-500 text-white shadow-sm hover:from-sky-600 hover:to-cyan-600 focus-visible:ring-sky-300"
                        >
                            ログイン
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="{{ $headerButtonBase }} border border-sky-200 bg-gradient-to-r from-sky-50 to-cyan-50 text-sky-800 hover:from-sky-100 hover:to-cyan-100 focus-visible:ring-sky-300"
                            >
                                新規登録
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>   

        <main class="w-full max-w-[340px] lg:max-w-5xl flex-grow mx-auto px-4 py-10">
            <section class="w-full">
                <div class="{{ $cardRadiusClass }} bg-white/70 backdrop-blur-lg border border-white/40 {{ $cardShadowClass }} {{ $cardPaddingHeroClass }}">
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
                    <div class="lg:col-span-2 {{ $cardRadiusClass }} bg-white/70 backdrop-blur-lg border border-white/40 {{ $cardShadowClass }} {{ $cardPaddingShopClass }}">
                        <h2 class="text-2xl font-bold text-slate-900 mb-4">お店のご案内</h2>
                        @if ($bodyBlocks->isNotEmpty())
                            @php $blockCount = $bodyBlocks->count(); @endphp
                            @if ($blockCount === 1)
                                {{-- 1件のみはシンプルカード表示 --}}
                                @foreach ($bodyBlocks as $block)
                                    <article class="{{ $cardRadiusClass }} border border-white/60 bg-white/80 {{ $cardShadowClass }} {{ $cardPaddingBlockClass }} flex flex-col gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-sm">1</span>
                                            <div>
                                                @php
                                                    $blockTitleSizeClass = \App\Support\WelcomeStyleHelper::blockTitleSizeClass(data_get($block, 'title_size'));
                                                    $blockTitleColorClass = \App\Support\WelcomeStyleHelper::blockTitleColorClass(data_get($block, 'title_color'));
                                                    $blockTextSizeClass = \App\Support\WelcomeStyleHelper::blockTextSizeClass(data_get($block, 'text_size'));
                                                    $blockTextColorClass = \App\Support\WelcomeStyleHelper::blockTextColorClass(data_get($block, 'text_color'));
                                                    $blockTextAlignClass = \App\Support\WelcomeStyleHelper::blockTextAlignClass(data_get($block, 'text_align'));
                                                    $blockMode = \App\Support\WelcomeStyleHelper::paragraphMode(data_get($block, 'paragraph_mode'));
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
                                            class="{{ $cardRadiusClass }} border border-white/60 bg-white/80 {{ $cardShadowClass }} {{ $cardPaddingBlockClass }} flex flex-col gap-4"
                                            @if ($index !== 0) style="display:none;" @endif
                                        >
                                            <div class="flex items-start gap-3">
                                                <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-sky-500 text-white font-bold text-sm">{{ $index + 1 }}</span>
                                                <div>
                                                    @php
                                                        $blockTitleSizeClass = \App\Support\WelcomeStyleHelper::blockTitleSizeClass(data_get($block, 'title_size'));
                                                        $blockTitleColorClass = \App\Support\WelcomeStyleHelper::blockTitleColorClass(data_get($block, 'title_color'));
                                                        $blockTextSizeClass = \App\Support\WelcomeStyleHelper::blockTextSizeClass(data_get($block, 'text_size'));
                                                        $blockTextColorClass = \App\Support\WelcomeStyleHelper::blockTextColorClass(data_get($block, 'text_color'));
                                                        $blockTextAlignClass = \App\Support\WelcomeStyleHelper::blockTextAlignClass(data_get($block, 'text_align'));
                                                        $blockMode = \App\Support\WelcomeStyleHelper::paragraphMode(data_get($block, 'paragraph_mode'));
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

                    <div id="store-info" class="scroll-mt-6 {{ $cardRadiusClass }} bg-gradient-to-br from-sky-200/70 via-white to-cyan-200/70 backdrop-blur-lg border border-white/50 {{ $cardShadowClass }} {{ $cardPaddingShopClass }}">
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
                            <p><span class="font-semibold text-slate-900">お問い合わせ:</span> {{ $settings->welcome_contact_number ?: 'インスタのDMにご連絡ください' }}</p>
                            <a
                                href="{{ $settings->welcome_instagram_url ?: 'https://www.instagram.com/06sorairo30' }}"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-orange-400 text-white font-bold shadow-md shadow-pink-200 hover:from-pink-600 hover:to-orange-500 transition"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Instagram
                            </a>
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