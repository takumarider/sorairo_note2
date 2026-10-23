<nav x-data="{ open: false }" class="bg-sky-100/70 backdrop-blur-md border-b border-sky-200 shadow-sm">
    @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser?->is_admin === true;
        $unreadMessageCount = $currentUser ? $currentUser->unreadAdminMessageCount() : 0;
        $unreadLabel = $unreadMessageCount > 99 ? '99+' : $unreadMessageCount;
    @endphp
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-white/70 ring-1 ring-sky-200 shadow-sm backdrop-blur">
                            <x-application-logo class="block h-7 w-auto fill-current text-sky-700" />
                        </div>
                        <span class="hidden sm:inline font-bold text-sky-900 tracking-wide">Sorairo</span>
                    </a>
                </div>

                <!-- Primary links -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('menus.index') }}"
                       class="px-3 py-2 rounded-lg text-sm font-semibold text-sky-800 hover:text-white hover:bg-gradient-to-r hover:from-sky-400 hover:to-blue-500 transition shadow-sm"
                       aria-label="メニュー一覧">
                        メニューを見る
                    </a>
                    @auth
                        <a href="{{ route('reservations.same-day.times') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-sky-800 hover:text-white hover:bg-gradient-to-r hover:from-emerald-400 hover:to-teal-500 transition shadow-sm"
                           aria-label="今日予約する（{{ $hasSameDayAvailability ? '受付中' : '準備中' }}）">
                            <span>今日予約する</span>
                            <x-reservation-status-badge :available="$hasSameDayAvailability" />
                        </a>
                        <a href="{{ route('mypage') }}"
                           class="px-3 py-2 rounded-lg text-sm font-semibold text-sky-800 hover:text-white hover:bg-gradient-to-r hover:from-sky-400 hover:to-blue-500 transition shadow-sm"
                           aria-label="マイページ{{ $unreadMessageCount > 0 ? '（未読'.$unreadLabel.'件）' : '' }}">
                            <span class="flex flex-col items-center leading-tight">
                                <span>マイページ</span>
                                @if($unreadMessageCount > 0)
                                    <span class="mt-0.5 inline-flex whitespace-nowrap rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold leading-none text-white">未読{{ $unreadLabel }}</span>
                                @endif
                            </span>
                        </a>
                    @endauth
                </div>
            </div>
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    @if($isAdmin)
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                           class="inline-flex items-center px-4 py-2 me-3 rounded-lg bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold shadow hover:from-amber-500 hover:to-orange-600 transition"
                           aria-label="管理画面へ切り替え">
                            管理画面へ
                        </a>
                    @endif

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-sky-200 text-sm leading-4 font-medium rounded-md text-sky-800 bg-white/80 hover:text-sky-900 hover:bg-white focus:outline-none transition ease-in-out duration-150 shadow-sm backdrop-blur">
                                <div>{{ $currentUser?->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if($isAdmin)
                                <x-dropdown-link :href="route('filament.admin.pages.dashboard')">
                                    {{ __('管理画面') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}"
                           class="px-3 py-2 rounded-lg text-sm font-semibold text-sky-800 bg-white/80 ring-1 ring-sky-200 hover:bg-white transition">
                            ログイン
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold text-white bg-sky-500 hover:bg-sky-600 transition shadow-sm">
                                新規登録
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-sky-700 hover:text-sky-900 hover:bg-white/70 focus:outline-none focus:bg-white/70 focus:text-sky-900 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid {{ $currentUser ? 'grid-cols-4' : 'grid-cols-3' }} gap-1.5 pb-2 sm:hidden">
            <a href="{{ route('menus.index') }}"
               class="inline-flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-1 py-2 text-xs font-semibold shadow-sm ring-1 ring-sky-200 transition-all duration-150 ease-out active:scale-95 active:text-white active:bg-gradient-to-r active:from-sky-400 active:to-blue-500 {{ request()->routeIs('menus.*') ? 'text-white bg-gradient-to-r from-sky-400 to-blue-500' : 'text-sky-800' }}"
               aria-label="メニュー">
                メニュー
            </a>
            @auth
                <a href="{{ route('reservations.same-day.times') }}"
                   class="inline-flex min-w-0 flex-col items-center justify-center gap-1 whitespace-nowrap rounded-lg px-1 py-1.5 text-xs font-semibold shadow-sm ring-1 ring-sky-200 transition-all duration-150 ease-out active:scale-95 active:text-white active:bg-gradient-to-r active:from-emerald-400 active:to-teal-500 {{ request()->routeIs('reservations.same-day.*') ? 'text-white bg-gradient-to-r from-emerald-400 to-teal-500' : 'text-sky-800' }}"
                   aria-label="今日予約する（{{ $hasSameDayAvailability ? '受付中' : '準備中' }}）">
                    <span>今日予約する</span>
                    <x-reservation-status-badge :available="$hasSameDayAvailability" />
                </a>
                <a href="{{ route('mypage') }}"
                   class="inline-flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-1 py-2 text-xs font-semibold shadow-sm ring-1 ring-sky-200 transition-all duration-150 ease-out active:scale-95 active:text-white active:bg-gradient-to-r active:from-sky-400 active:to-blue-500 {{ request()->routeIs('mypage') ? 'text-white bg-gradient-to-r from-sky-400 to-blue-500' : 'text-sky-800' }}"
                   aria-label="マイページ{{ $unreadMessageCount > 0 ? '（未読'.$unreadLabel.'件）' : '' }}">
                    <span class="flex flex-col items-center leading-tight">
                        <span class="whitespace-nowrap">マイページ</span>
                        @if($unreadMessageCount > 0)
                            <span class="mt-0.5 inline-flex whitespace-nowrap rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold leading-none text-white">未読{{ $unreadLabel }}</span>
                        @endif
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="contents">
                    @csrf
                    <button type="submit"
                            class="inline-flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-1 py-2 text-xs font-semibold shadow-sm ring-1 ring-sky-200 text-sky-800 transition-all duration-150 ease-out active:scale-95 active:text-white active:bg-gradient-to-r active:from-sky-400 active:to-blue-500">
                        ログアウト
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-1 py-2 text-xs font-semibold shadow-sm ring-1 ring-sky-200 text-sky-800 bg-white/80 transition-all duration-150 ease-out active:scale-95 active:bg-white active:text-sky-900"
                   aria-label="ログイン">
                    ログイン
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-1 py-2 text-xs font-semibold text-white bg-sky-500 shadow-sm transition-all duration-150 ease-out active:scale-95 active:bg-sky-600"
                       aria-label="新規登録">
                        新規登録
                    </a>
                @else
                    <span class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-[13px] font-semibold shadow-sm bg-white/80 text-sky-800 ring-1 ring-sky-200">
                        ご案内
                    </span>
                @endif
            @endauth
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/80 backdrop-blur border-t border-sky-200">
        @auth
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('お知らせ') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('reservations.same-day.times')" :active="request()->routeIs('reservations.same-day.*')" aria-label="今日予約する（{{ $hasSameDayAvailability ? '受付中' : '準備中' }}）">
                    <span class="flex items-center justify-between gap-2">
                        <span>{{ __('今日予約する') }}</span>
                        <x-reservation-status-badge :available="$hasSameDayAvailability" />
                    </span>
                </x-responsive-nav-link>

                @if($isAdmin)
                    <x-responsive-nav-link :href="route('filament.admin.pages.dashboard')" :active="request()->routeIs('filament.admin.*')">
                        {{ __('管理画面へ切り替え') }}
                    </x-responsive-nav-link>
                @endif
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-sky-200/70 bg-white/90">
                <div class="px-4">
                    <div class="font-medium text-base text-sky-900">{{ $currentUser?->name }}</div>
                    <div class="font-medium text-sm text-sky-600">{{ $currentUser?->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('プロフィール') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('ログアウト') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('menus.index')" :active="request()->routeIs('menus.*')">
                    {{ __('メニューを見る') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                    {{ __('ログイン') }}
                </x-responsive-nav-link>
                @if (Route::has('register'))
                    <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                        {{ __('新規登録') }}
                    </x-responsive-nav-link>
                @endif
            </div>
        @endauth
    </div>
</nav>
