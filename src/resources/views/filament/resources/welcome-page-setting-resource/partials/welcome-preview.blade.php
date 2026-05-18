<div class="space-y-4">
    @php
        $themeBackgroundClass = [
            'sky' => 'bg-gradient-to-br from-sky-50 via-white to-cyan-100',
            'mint' => 'bg-gradient-to-br from-emerald-50 via-white to-teal-100',
            'sand' => 'bg-gradient-to-br from-amber-50 via-white to-orange-100',
        ][$theme_background ?? ''] ?? 'bg-gradient-to-br from-sky-50 via-white to-cyan-100';

        $accentClasses = [
            'sky' => ['badge' => 'bg-sky-100 text-sky-800', 'button' => 'border-sky-200 text-sky-800'],
            'emerald' => ['badge' => 'bg-emerald-100 text-emerald-800', 'button' => 'border-emerald-200 text-emerald-800'],
            'rose' => ['badge' => 'bg-rose-100 text-rose-800', 'button' => 'border-rose-200 text-rose-800'],
        ][$theme_accent ?? ''] ?? ['badge' => 'bg-sky-100 text-sky-800', 'button' => 'border-sky-200 text-sky-800'];

        $heroAlignClass = ['left' => 'text-left', 'center' => 'text-center'][$hero_text_align ?? ''] ?? 'text-left';
        $heroTitleSizeClass = ['md' => 'text-2xl', 'lg' => 'text-3xl', 'xl' => 'text-4xl'][$hero_title_size ?? ''] ?? 'text-3xl';
        $heroTitleColorClass = ['slate' => 'text-slate-900', 'sky' => 'text-sky-900', 'emerald' => 'text-emerald-900'][$hero_title_color ?? ''] ?? 'text-slate-900';
        $heroSubtitleSizeClass = ['sm' => 'text-base', 'md' => 'text-lg', 'lg' => 'text-xl'][$hero_subtitle_size ?? ''] ?? 'text-lg';
        $heroSubtitleColorClass = ['sky' => 'text-sky-800', 'emerald' => 'text-emerald-800', 'rose' => 'text-rose-800'][$hero_subtitle_color ?? ''] ?? 'text-sky-800';
        $heroLeadSizeClass = ['sm' => 'text-sm', 'md' => 'text-base', 'lg' => 'text-lg'][$hero_lead_size ?? ''] ?? 'text-base';
        $heroLeadColorClass = ['slate' => 'text-slate-600', 'sky' => 'text-sky-700', 'emerald' => 'text-emerald-700'][$hero_lead_color ?? ''] ?? 'text-slate-600';
        $heroLeadMode = in_array($hero_lead_paragraph_mode ?? null, ['line', 'paragraph'], true) ? $hero_lead_paragraph_mode : 'line';
        $shopParagraphMode = in_array($shop_paragraph_mode ?? null, ['line', 'paragraph'], true) ? $shop_paragraph_mode : 'line';
        $shopTitleSizeClass = ['sm' => 'text-base', 'md' => 'text-lg', 'lg' => 'text-xl'][$shop_title_size ?? ''] ?? 'text-lg';
        $shopTitleColorClass = ['slate' => 'text-slate-900', 'sky' => 'text-sky-900', 'emerald' => 'text-emerald-900'][$shop_title_color ?? ''] ?? 'text-slate-900';
        $shopBodySizeClass = ['sm' => 'text-xs', 'md' => 'text-sm', 'lg' => 'text-base'][$shop_body_size ?? ''] ?? 'text-sm';
        $shopBodyColorClass = ['slate' => 'text-slate-700', 'sky' => 'text-sky-800', 'emerald' => 'text-emerald-800'][$shop_body_color ?? ''] ?? 'text-slate-700';

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

    <div class="space-y-4 rounded-lg {{ $themeBackgroundClass }} p-6">
        <div class="rounded-2xl bg-white/70 p-6 backdrop-blur-lg">
            <div class="space-y-3 {{ $heroAlignClass }}">
                @if ($hero_badge ?? null)
                    <span class="inline-block rounded-full px-3 py-1 text-sm font-semibold {{ $accentClasses['badge'] }}">
                        {{ $hero_badge }}
                    </span>
                @endif

                @if ($hero_title ?? null)
                    <h1 class="{{ $heroTitleSizeClass }} font-extrabold {{ $heroTitleColorClass }}">{{ $hero_title }}</h1>
                @else
                    <p class="text-sm italic text-slate-500">メイン見出しを入力してください</p>
                @endif

                @if ($hero_subtitle ?? null)
                    <p class="{{ $heroSubtitleSizeClass }} font-semibold {{ $heroSubtitleColorClass }}">{{ $hero_subtitle }}</p>
                @endif

                @if ($hero_lead ?? null)
                    @if ($heroLeadMode === 'paragraph')
                        @foreach ($toParagraphs($hero_lead) as $paragraph)
                            <p class="{{ $heroLeadSizeClass }} {{ $heroLeadColorClass }} whitespace-pre-line">{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p class="{{ $heroLeadSizeClass }} {{ $heroLeadColorClass }} whitespace-pre-line">{{ $hero_lead }}</p>
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
                        <div class="rounded-xl border border-white/60 bg-white/80 p-4 shadow-sm">
                            <div class="flex items-start gap-2">
                                <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-bold text-white">1</span>
                                <div class="flex-1">
                                    @php
                                        $blockTitleSizeClass = ['sm' => 'text-sm', 'md' => 'text-base', 'lg' => 'text-lg'][$block['title_size'] ?? ''] ?? 'text-base';
                                        $blockTitleColorClass = ['slate' => 'text-slate-900', 'sky' => 'text-sky-900', 'emerald' => 'text-emerald-900'][$block['title_color'] ?? ''] ?? 'text-slate-900';
                                        $blockTextSizeClass = ['sm' => 'text-xs', 'md' => 'text-sm', 'lg' => 'text-base'][$block['text_size'] ?? ''] ?? 'text-sm';
                                        $blockTextColorClass = ['slate' => 'text-slate-600', 'sky' => 'text-sky-700', 'emerald' => 'text-emerald-700'][$block['text_color'] ?? ''] ?? 'text-slate-600';
                                        $blockTextAlignClass = ['left' => 'text-left', 'center' => 'text-center'][$block['text_align'] ?? ''] ?? 'text-left';
                                        $blockMode = in_array($block['paragraph_mode'] ?? null, ['line', 'paragraph'], true) ? $block['paragraph_mode'] : 'line';
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
                                class="rounded-xl border border-white/60 bg-white/80 p-4 shadow-sm"
                                style="{{ $index !== 0 ? 'display:none;' : '' }}"
                            >
                                <div class="flex items-start gap-2">
                                    <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-bold text-white">{{ $index + 1 }}</span>
                                    <div class="flex-1">
                                        @php
                                            $blockTitleSizeClass = ['sm' => 'text-sm', 'md' => 'text-base', 'lg' => 'text-lg'][$block['title_size'] ?? ''] ?? 'text-base';
                                            $blockTitleColorClass = ['slate' => 'text-slate-900', 'sky' => 'text-sky-900', 'emerald' => 'text-emerald-900'][$block['title_color'] ?? ''] ?? 'text-slate-900';
                                            $blockTextSizeClass = ['sm' => 'text-xs', 'md' => 'text-sm', 'lg' => 'text-base'][$block['text_size'] ?? ''] ?? 'text-sm';
                                            $blockTextColorClass = ['slate' => 'text-slate-600', 'sky' => 'text-sky-700', 'emerald' => 'text-emerald-700'][$block['text_color'] ?? ''] ?? 'text-slate-600';
                                            $blockTextAlignClass = ['left' => 'text-left', 'center' => 'text-center'][$block['text_align'] ?? ''] ?? 'text-left';
                                            $blockMode = in_array($block['paragraph_mode'] ?? null, ['line', 'paragraph'], true) ? $block['paragraph_mode'] : 'line';
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

        <div class="rounded-2xl border border-white/50 bg-gradient-to-br from-sky-200/70 via-white to-cyan-200/70 p-6 backdrop-blur-lg">
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
                    {{ $shop_contact_number ?? '未設定' }}
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
        保存前の入力内容をそのまま表示しています。
    </p>
</div>