<div class="space-y-4" x-data="{ viewport: 'desktop' }">
    @php
        $themeBackgroundClass = \App\Support\WelcomeStyleHelper::themeBackgroundClass($theme_background ?? null);
        $accentClasses = \App\Support\WelcomeStyleHelper::accentClasses($theme_accent ?? null);
        $heroAlignClass = \App\Support\WelcomeStyleHelper::heroAlignClass($hero_text_align ?? null);
        $heroTitleSizeClass = \App\Support\WelcomeStyleHelper::heroTitleSizeClass($hero_title_size ?? null);
        $heroTitleColorClass = \App\Support\WelcomeStyleHelper::heroTitleColorClass($hero_title_color ?? null);
        $heroSubtitleSizeClass = \App\Support\WelcomeStyleHelper::heroSubtitleSizeClass($hero_subtitle_size ?? null);
        $heroSubtitleColorClass = \App\Support\WelcomeStyleHelper::heroSubtitleColorClass($hero_subtitle_color ?? null);
        $heroLeadSizeClass = \App\Support\WelcomeStyleHelper::heroLeadSizeClass($hero_lead_size ?? null);
        $heroLeadColorClass = \App\Support\WelcomeStyleHelper::heroLeadColorClass($hero_lead_color ?? null);
        $heroLeadMode = \App\Support\WelcomeStyleHelper::paragraphMode($hero_lead_paragraph_mode ?? null);
        $shopParagraphMode = \App\Support\WelcomeStyleHelper::paragraphMode($shop_paragraph_mode ?? null);
        $shopTitleSizeClass = \App\Support\WelcomeStyleHelper::shopTitleSizeClass($shop_title_size ?? null);
        $shopTitleColorClass = \App\Support\WelcomeStyleHelper::shopTitleColorClass($shop_title_color ?? null);
        $shopBodySizeClass = \App\Support\WelcomeStyleHelper::shopBodySizeClass($shop_body_size ?? null);
        $shopBodyColorClass = \App\Support\WelcomeStyleHelper::shopBodyColorClass($shop_body_color ?? null);
        $cardPaddingHeroClass = \App\Support\WelcomeStyleHelper::cardPaddingHeroClass($card_padding ?? null);
        $cardPaddingShopClass = \App\Support\WelcomeStyleHelper::cardPaddingShopClass($card_padding ?? null);
        $cardPaddingBlockClass = \App\Support\WelcomeStyleHelper::cardPaddingBlockClass($card_padding ?? null);
        $cardRadiusClass = \App\Support\WelcomeStyleHelper::cardRadiusClass($card_radius ?? null);
        $cardShadowClass = \App\Support\WelcomeStyleHelper::cardShadowClass($card_shadow ?? null);
        $fontStyleClass = \App\Support\WelcomeStyleHelper::fontStyleClass($font_style ?? null);
        $cardPaddingHeroMobileClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($cardPaddingHeroClass, 'mobile');
        $cardPaddingHeroDesktopClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($cardPaddingHeroClass, 'desktop');
        $cardPaddingShopMobileClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($cardPaddingShopClass, 'mobile');
        $cardPaddingShopDesktopClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($cardPaddingShopClass, 'desktop');

        $heroTitleSizeMobileClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroTitleSizeClass, 'mobile');
        $heroTitleSizeDesktopClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroTitleSizeClass, 'desktop');
        $heroSubtitleSizeMobileClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroSubtitleSizeClass, 'mobile');
        $heroSubtitleSizeDesktopClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroSubtitleSizeClass, 'desktop');
        $heroLeadSizeMobileClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroLeadSizeClass, 'mobile');
        $heroLeadSizeDesktopClass = \App\Support\WelcomeStyleHelper::previewResponsiveClass($heroLeadSizeClass, 'desktop');

        $toParagraphs = static function (?string $text): array {
            if (! filled($text)) {
                return [];
            }

            $normalized = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
            $parts = preg_split('/\n{2,}/', $normalized) ?: [];

            return array_values(array_filter($parts, static fn (string $part): bool => filled(trim($part))));
        };
    @endphp
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

    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-500">表示幅:</span>
        <button type="button" @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="rounded-full px-3 py-1 text-xs font-semibold">Mobile</button>
        <button type="button" @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="rounded-full px-3 py-1 text-xs font-semibold">Tablet</button>
        <button type="button" @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="rounded-full px-3 py-1 text-xs font-semibold">Desktop</button>
    </div>

    <div class="mx-auto space-y-4 rounded-lg {{ $themeBackgroundClass }} {{ $fontStyleClass }} p-6 transition-all duration-200" :class="viewport === 'mobile' ? 'max-w-[340px]' : (viewport === 'tablet' ? 'max-w-3xl' : 'max-w-5xl')">
        <div
            :class="viewport === 'mobile' ? '{{ $cardPaddingHeroMobileClass }}' : '{{ $cardPaddingHeroDesktopClass }}'"
            class="{{ $cardRadiusClass }} bg-white/70 backdrop-blur-lg {{ $cardShadowClass }}"
        >
            <div class="space-y-3 {{ $heroAlignClass }}">
                @if ($hero_badge ?? null)
                    <span class="inline-block rounded-full px-3 py-1 text-sm font-semibold {{ $accentClasses['badge'] }}">
                        {{ $hero_badge }}
                    </span>
                @endif

                @if ($hero_title ?? null)
                    <h1 x-bind:class="viewport === 'mobile' ? '{{ $heroTitleSizeMobileClass }}' : '{{ $heroTitleSizeDesktopClass }}'" class="font-extrabold {{ $heroTitleColorClass }}">{{ $hero_title }}</h1>
                @else
                    <p class="text-sm italic text-slate-500">メイン見出しを入力してください</p>
                @endif

                @if ($hero_subtitle ?? null)
                    <p x-bind:class="viewport === 'mobile' ? '{{ $heroSubtitleSizeMobileClass }}' : '{{ $heroSubtitleSizeDesktopClass }}'" class="font-semibold {{ $heroSubtitleColorClass }}">{{ $hero_subtitle }}</p>
                @endif

                @if ($hero_lead ?? null)
                    @if ($heroLeadMode === 'paragraph')
                        @foreach ($toParagraphs($hero_lead) as $paragraph)
                            <p x-bind:class="viewport === 'mobile' ? '{{ $heroLeadSizeMobileClass }}' : '{{ $heroLeadSizeDesktopClass }}'" class="{{ $heroLeadColorClass }} whitespace-pre-line">{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p x-bind:class="viewport === 'mobile' ? '{{ $heroLeadSizeMobileClass }}' : '{{ $heroLeadSizeDesktopClass }}'" class="{{ $heroLeadColorClass }} whitespace-pre-line">{{ $hero_lead }}</p>
                    @endif
                @else
                    <p class="text-sm italic text-slate-500">リード文を入力してください</p>
                @endif

                @if ($hero_image ?? null)
                    <div class="mt-4 overflow-hidden rounded-xl bg-slate-100 p-2">
                        <img src="{{ $hero_image }}" alt="hero" class="h-auto max-h-[420px] w-full object-contain">
                    </div>
                @endif
            </div>
        </div>

        @if ($body_blocks && count($body_blocks) > 0)
            @php $previewBlockCount = count($body_blocks); @endphp
            <div class="space-y-3">
                <h2 class="font-semibold text-slate-900">本文セクション</h2>
                @if ($previewBlockCount === 1)
                    @foreach ($body_blocks as $index => $block)
                        <div class="{{ $cardRadiusClass }} border border-white/60 bg-white/80 {{ $cardPaddingBlockClass }} {{ $cardShadowClass }}">
                            <div class="flex items-start gap-2">
                                <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-bold text-white">1</span>
                                <div class="flex-1">
                                    @php
                                        $blockTitleSizeClass = \App\Support\WelcomeStyleHelper::blockTitleSizeClass($block['title_size'] ?? null);
                                        $blockTitleColorClass = \App\Support\WelcomeStyleHelper::blockTitleColorClass($block['title_color'] ?? null);
                                        $blockTextSizeClass = \App\Support\WelcomeStyleHelper::blockTextSizeClass($block['text_size'] ?? null);
                                        $blockTextColorClass = \App\Support\WelcomeStyleHelper::blockTextColorClass($block['text_color'] ?? null);
                                        $blockTextAlignClass = \App\Support\WelcomeStyleHelper::blockTextAlignClass($block['text_align'] ?? null);
                                        $blockMode = \App\Support\WelcomeStyleHelper::paragraphMode($block['paragraph_mode'] ?? null);
                                    @endphp
                                    <h3 class="font-semibold {{ $blockTitleSizeClass }} {{ $blockTitleColorClass }}">{{ $block['title'] ?? '（見出しなし）' }}</h3>
                                    @if ($blockMode === 'paragraph')
                                        @foreach ($toParagraphs($block['text'] ?? null) as $paragraph)
                                            <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $paragraph }}</p>
                                        @endforeach
                                    @else
                                        <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $block['text'] ?? '（本文なし）' }}</p>
                                    @endif
                                </div>
                            </div>
                            @if ($block['image'] ?? null)
                                <div class="mt-3 overflow-hidden rounded-lg bg-slate-100 p-2">
                                    <img src="{{ $block['image'] }}" alt="block" class="h-auto max-h-[320px] w-full object-contain">
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div
                        x-data="{
                            current: 0,
                            total: {{ $previewBlockCount }},
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
                        @foreach ($body_blocks as $index => $block)
                            <div
                                x-show="current === {{ $index }}"
                                x-transition:enter.duration.350ms
                                :class="direction === 'right' ? 'slide-enter-right' : 'slide-enter-left'"
                                class="{{ $cardRadiusClass }} border border-white/60 bg-white/80 {{ $cardPaddingBlockClass }} {{ $cardShadowClass }}"
                                @if ($index !== 0) style="display:none;" @endif
                            >
                                <div class="flex items-start gap-2">
                                    <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-bold text-white">{{ $index + 1 }}</span>
                                    <div class="flex-1">
                                        @php
                                            $blockTitleSizeClass = \App\Support\WelcomeStyleHelper::blockTitleSizeClass($block['title_size'] ?? null);
                                            $blockTitleColorClass = \App\Support\WelcomeStyleHelper::blockTitleColorClass($block['title_color'] ?? null);
                                            $blockTextSizeClass = \App\Support\WelcomeStyleHelper::blockTextSizeClass($block['text_size'] ?? null);
                                            $blockTextColorClass = \App\Support\WelcomeStyleHelper::blockTextColorClass($block['text_color'] ?? null);
                                            $blockTextAlignClass = \App\Support\WelcomeStyleHelper::blockTextAlignClass($block['text_align'] ?? null);
                                            $blockMode = \App\Support\WelcomeStyleHelper::paragraphMode($block['paragraph_mode'] ?? null);
                                        @endphp
                                        <h3 class="font-semibold {{ $blockTitleSizeClass }} {{ $blockTitleColorClass }}">{{ $block['title'] ?? '（見出しなし）' }}</h3>
                                        @if ($blockMode === 'paragraph')
                                            @foreach ($toParagraphs($block['text'] ?? null) as $paragraph)
                                                <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $paragraph }}</p>
                                            @endforeach
                                        @else
                                            <p class="mt-1 whitespace-pre-line {{ $blockTextSizeClass }} {{ $blockTextColorClass }} {{ $blockTextAlignClass }}">{{ $block['text'] ?? '（本文なし）' }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if ($block['image'] ?? null)
                                    <div class="mt-3 overflow-hidden rounded-lg bg-slate-100 p-2">
                                        <img src="{{ $block['image'] }}" alt="block" class="h-auto max-h-[320px] w-full object-contain">
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="mt-3 flex items-center justify-between">
                            <button @click="prev()" :disabled="sliding" class="flex items-center gap-1 rounded-full border border-sky-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-sky-700 shadow transition hover:bg-sky-50">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                前へ
                            </button>
                            <div class="flex gap-1.5">
                                @foreach ($body_blocks as $index => $block)
                                    <button @click="current = {{ $index }}" :class="current === {{ $index }} ? 'bg-sky-500 w-4' : 'bg-sky-200 w-2'" class="h-2 rounded-full transition-all duration-300"></button>
                                @endforeach
                            </div>
                            <button @click="next()" :disabled="sliding" class="flex items-center gap-1 rounded-full border border-sky-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-sky-700 shadow transition hover:bg-sky-50">
                                次へ
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        <p class="mt-1 text-center text-xs text-slate-400"><span x-text="current + 1">1</span> / {{ $previewBlockCount }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-center">
                <p class="text-sm text-slate-600">本文セクションを追加してください</p>
            </div>
        @endif

        <div
            :class="viewport === 'mobile' ? '{{ $cardPaddingShopMobileClass }}' : '{{ $cardPaddingShopDesktopClass }}'"
            class="{{ $cardRadiusClass }} border border-white/50 bg-gradient-to-br from-sky-200/70 via-white to-cyan-200/70 backdrop-blur-lg {{ $cardShadowClass }}"
        >
            <h3 class="mb-3 font-bold {{ $shopTitleSizeClass }} {{ $shopTitleColorClass }}">
                {{ $shop_title ?? '店舗情報' }}
            </h3>
            <div class="space-y-2 {{ $shopBodySizeClass }} {{ $shopBodyColorClass }}">
                @if ($shop_description ?? null)
                    @if ($shopParagraphMode === 'paragraph')
                        @foreach ($toParagraphs($shop_description) as $paragraph)
                            <p class="whitespace-pre-line">{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p class="whitespace-pre-line">{{ $shop_description }}</p>
                    @endif
                @endif

                <p class="whitespace-pre-line">
                    <span class="font-semibold text-slate-900">営業時間:</span>
                    {{ $shop_hours ?? '未設定' }}
                </p>

                <p>
                    <span class="font-semibold text-slate-900">定休日:</span>
                    {{ $shop_holiday ?? '未設定' }}
                </p>

                <p>
                    <span class="font-semibold text-slate-900">お問い合わせ:</span>
                    {{ filled($shop_contact_number) ? $shop_contact_number : 'インスタのDMにご連絡ください' }}
                </p>

                @if ($shop_note ?? null)
                    @if ($shopParagraphMode === 'paragraph')
                        @foreach ($toParagraphs($shop_note) as $paragraph)
                            <p class="pt-1 whitespace-pre-line">{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p class="pt-1 whitespace-pre-line">{{ $shop_note }}</p>
                    @endif
                @endif

                @if ($instagram_url ?? null)
                    <div class="pt-2">
                        <a href="{{ $instagram_url }}" target="_blank" rel="noopener noreferrer" class="inline-block rounded-full border bg-white/80 px-3 py-1.5 text-sm font-semibold hover:bg-white {{ $accentClasses['button'] }}">
                            Instagram
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <p class="text-xs text-slate-500">
        保存前の入力内容をそのまま表示しています。Mobileはモバイル用クラス、Tablet/Desktopは大型画面用クラスでプレビューします。
    </p>
</div>