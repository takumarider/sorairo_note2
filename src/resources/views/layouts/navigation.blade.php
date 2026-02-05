<nav x-data="{ open: false }" class="bg-sky-100/70 backdrop-blur-md border-b border-sky-200 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
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
                        メニュー
                    </a>
                    <a href="{{ route('mypage') }}"
                       class="px-3 py-2 rounded-lg text-sm font-semibold text-sky-800 hover:text-white hover:bg-gradient-to-r hover:from-sky-400 hover:to-blue-500 transition shadow-sm"
                       aria-label="マイページ">
                        マイページ
                    </a>
                </div>
            </div>
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-sky-200 text-sm leading-4 font-medium rounded-md text-sky-800 bg-white/80 hover:text-sky-900 hover:bg-white focus:outline-none transition ease-in-out duration-150 shadow-sm backdrop-blur">
                            <div>{{ Auth::user()->name }}</div>

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

                        @if(auth()->check() && auth()->user()->is_admin)
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
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/80 backdrop-blur border-t border-sky-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('お知らせ') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('menus.index')" :active="request()->routeIs('menus.*')">
                メニュー
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('mypage')" :active="request()->routeIs('mypage')">
                マイページ
            </x-responsive-nav-link>
            
            @if(auth()->check() && auth()->user()->is_admin)
                <x-responsive-nav-link :href="route('filament.admin.pages.dashboard')" :active="request()->routeIs('filament.admin.*')">
                    {{ __('管理画面') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-sky-200/70 bg-white/90">
            <div class="px-4">
                <div class="font-medium text-base text-sky-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-sky-600">{{ Auth::user()->email }}</div>
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
    </div>
</nav>
