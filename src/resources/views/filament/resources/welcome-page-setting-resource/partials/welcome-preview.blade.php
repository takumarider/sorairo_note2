<div class="space-y-4">
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

    <div class="space-y-4 rounded-lg bg-gradient-to-br from-sky-50 via-white to-cyan-100 p-6">
        <div class="rounded-2xl bg-white/70 p-6 backdrop-blur-lg">
            <div class="space-y-3">
                @if ($hero_badge ?? null)
                    <span class="inline-block rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800">
                        {{ $hero_badge }}
                    </span>
                @endif

                @if ($hero_title ?? null)
                    <h1 class="text-3xl font-extrabold text-slate-900">{{ $hero_title }}</h1>
                @else
                    <p class="text-sm italic text-slate-500">メイン見出しを入力してください</p>
                @endif

                @if ($hero_subtitle ?? null)
                    <p class="text-lg font-semibold text-sky-800">{{ $hero_subtitle }}</p>
                @endif

                @if ($hero_lead ?? null)
                    <p class="text-base text-slate-600">{{ $hero_lead }}</p>
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
                                    <h3 class="font-semibold text-slate-900">{{ $block['title'] ?? '（見出しなし）' }}</h3>
                                    <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $block['text'] ?? '（本文なし）' }}</p>
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
                                        <h3 class="font-semibold text-slate-900">{{ $block['title'] ?? '（見出しなし）' }}</h3>
                                        <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $block['text'] ?? '（本文なし）' }}</p>
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
            <h3 class="mb-3 text-lg font-bold text-slate-900">
                {{ $shop_title ?? '店舗情報' }}
            </h3>
            <div class="space-y-2 text-sm text-slate-700">
                @if ($shop_description ?? null)
                    <p class="whitespace-pre-line">{{ $shop_description }}</p>
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
                    <p class="pt-1 whitespace-pre-line text-slate-600">{{ $shop_note }}</p>
                @endif

                @if ($instagram_url ?? null)
                    <div class="pt-2">
                        <a href="{{ $instagram_url }}" target="_blank" rel="noopener noreferrer" class="inline-block rounded-full border border-sky-200 bg-white/80 px-3 py-1.5 text-sm font-semibold text-sky-800 hover:bg-white">
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